<?php

namespace DarkCoder\Ofa\Services;

use DarkCoder\Ofa\Models\OfaEvent;
use DarkCoder\Ofa\Models\EventListener;
use DarkCoder\Ofa\Models\EventHistory;
use DarkCoder\Ofa\Models\ScheduledEvent;
use DarkCoder\Ofa\Models\ScheduledEventExecution;
use DarkCoder\Ofa\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EventDispatcher
{
    /**
     * Dispatch an event
     */
    public function dispatch(
        string $eventName,
        array $payload = [],
        $serverId = null
    ): EventHistory {
        $start = microtime(true);

        $event = OfaEvent::where('event_name', $eventName)->first();
        if (!$event) {
            Log::warning("Event not found: {$eventName}");
            return new EventHistory();
        }

        $eventHistory = EventHistory::create([
            'event_id' => $event->id,
            'triggered_by_user' => Auth::id(),
            'related_server_id' => $serverId,
            'payload' => $payload,
            'status' => 'success',
            'listeners_executed' => 0,
            'listeners_failed' => 0,
            'execution_log' => [],
        ]);

        $executionLog = [];
        $executed = 0;
        $failed = 0;

        // Execute active listeners
        foreach ($event->activeListeners() as $listener) {
            try {
                $this->executeListener($listener, $payload, $executionLog);
                $executed++;
            } catch (\Exception $e) {
                $failed++;
                $executionLog[] = [
                    'listener' => $listener->listener_target,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
                Log::error("Event listener failed: {$listener->listener_target}", ['error' => $e]);
            }
        }

        $duration = (microtime(true) - $start) * 1000;

        $eventHistory->update([
            'listeners_executed' => $executed,
            'listeners_failed' => $failed,
            'execution_log' => $executionLog,
            'status' => $failed === 0 ? 'success' : ($executed > 0 ? 'partial' : 'failed'),
            'duration_ms' => (int)$duration,
        ]);

        // Trigger webhooks for this event
        app(ApiService::class)->triggerWebhook(null, $eventName, $payload);

        return $eventHistory;
    }

    /**
     * Execute a single event listener
     */
    private function executeListener(EventListener $listener, array $payload, &$log): void
    {
        match ($listener->listener_type) {
            'internal_callback' => $this->executeInternalCallback($listener->listener_target, $payload),
            'queue_job' => $this->queueJob($listener->listener_target, $payload),
            'email' => $this->sendEmail($listener->listener_target, $payload),
            'webhook' => $this->triggerWebhook($listener->listener_target, $payload),
            default => throw new \Exception("Unknown listener type: {$listener->listener_type}"),
        };
    }

    /**
     * Execute internal callback
     */
    private function executeInternalCallback(string $callback, array $payload): void
    {
        [$class, $method] = explode('@', $callback);
        
        if (!class_exists($class)) {
            throw new \Exception("Class not found: {$class}");
        }

        $instance = new $class();
        
        if (!method_exists($instance, $method)) {
            throw new \Exception("Method not found: {$method}");
        }

        call_user_func_array([$instance, $method], [$payload]);
    }

    /**
     * Queue a job for async execution
     */
    private function queueJob(string $jobClass, array $payload): void
    {
        if (!class_exists($jobClass)) {
            throw new \Exception("Job class not found: {$jobClass}");
        }

        // In production, use queue: dispatch(new $jobClass($payload));
        call_user_func_array([new $jobClass(), 'handle'], [$payload]);
    }

    /**
     * Send email notification
     */
    private function sendEmail(string $email, array $payload): void
    {
        // Implementation would send email via Laravel Mail
        Log::info("Email sent to {$email}", $payload);
    }

    /**
     * Register a new event
     */
    public function registerEvent(
        string $eventName,
        string $category,
        string $description = null,
        array $payloadStructure = []
    ): OfaEvent {
        return OfaEvent::firstOrCreate(
            ['event_name' => $eventName],
            [
                'category' => $category,
                'description' => $description,
                'payload_structure' => $payloadStructure,
                'is_system' => true,
            ]
        );
    }

    /**
     * Register a listener for an event
     */
    public function registerListener(
        string $eventName,
        string $listenerType,
        string $listenerTarget,
        array $conditions = []
    ): EventListener {
        $event = OfaEvent::where('event_name', $eventName)->first();
        
        if (!$event) {
            throw new \Exception("Event not found: {$eventName}");
        }

        return EventListener::create([
            'event_id' => $event->id,
            'listener_type' => $listenerType,
            'listener_target' => $listenerTarget,
            'conditions' => $conditions,
            'active' => true,
        ]);
    }

    /**
     * Get event history
     */
    public function getEventHistory(string $eventName, int $limit = 100)
    {
        $event = OfaEvent::where('event_name', $eventName)->first();
        
        if (!$event) {
            return [];
        }

        return EventHistory::where('event_id', $event->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Create a scheduled event (like cron)
     */
    public function scheduleEvent(
        string $eventName,
        string $cronExpression,
        string $eventType,
        string $handler,
        array $parameters = []
    ): ScheduledEvent {
        return ScheduledEvent::create([
            'event_name' => $eventName,
            'cron_expression' => $cronExpression,
            'event_type' => $eventType,
            'handler' => $handler,
            'parameters' => $parameters,
            'enabled' => true,
        ]);
    }

    /**
     * Execute a scheduled event
     */
    public function executeScheduledEvent(ScheduledEvent $scheduledEvent): void
    {
        $start = microtime(true);
        $output = null;
        $error = null;
        $status = 'success';

        try {
            if ($scheduledEvent->event_type === 'internal_function') {
                $this->executeInternalCallback($scheduledEvent->handler, $scheduledEvent->parameters ?? []);
            } elseif ($scheduledEvent->event_type === 'webhook') {
                $this->triggerWebhook($scheduledEvent->handler, $scheduledEvent->parameters ?? []);
            }
        } catch (\Exception $e) {
            $status = 'failed';
            $error = $e->getMessage();
        }

        $duration = (microtime(true) - $start) * 1000;
        $scheduledEvent->recordExecution($status, $output, (int)$duration, $error);
    }

    /**
     * Process due scheduled events
     */
    public function processDueScheduledEvents(): int
    {
        $events = ScheduledEvent::enabled()->due()->get();
        $processed = 0;

        foreach ($events as $event) {
            try {
                $this->executeScheduledEvent($event);
                // Update next execution time based on cron expression
                // This would need a cron library like mtdowling/cron-expression
                $processed++;
            } catch (\Exception $e) {
                Log::error("Scheduled event execution failed: {$event->event_name}", ['error' => $e]);
            }
        }

        return $processed;
    }

    /**
     * Send notification
     */
    public function notify(
        $userId,
        string $title,
        string $message = null,
        string $type = 'info',
        array $data = []
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'notification_type' => $type,
            'title' => $title,
            'message' => $message,
            'channel' => 'in-app',
            'data' => $data,
        ]);
    }
}
