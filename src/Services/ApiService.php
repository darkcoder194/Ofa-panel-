<?php

namespace DarkCoder\Ofa\Services;

use DarkCoder\Ofa\Models\ApiKey;
use DarkCoder\Ofa\Models\ApiRequestLog;
use DarkCoder\Ofa\Models\Webhook;
use DarkCoder\Ofa\Models\WebhookDelivery;
use DarkCoder\Ofa\Models\ApiThrottle;
use DarkCoder\Ofa\Models\RateLimitRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ApiService
{
    /**
     * Create a new API key
     */
    public function createApiKey(
        $userId,
        string $name,
        array $permissions = [],
        array $restrictedServers = null,
        $expiresAt = null
    ): ApiKey {
        $keyPair = ApiKey::generateKey();

        return ApiKey::create([
            'user_id' => $userId,
            'name' => $name,
            'key' => $keyPair['key'],
            'secret' => $keyPair['secret'],
            'permissions' => $permissions,
            'restricted_servers' => $restrictedServers,
            'expires_at' => $expiresAt,
            'active' => true,
        ]);
    }

    /**
     * Validate API key
     */
    public function validateApiKey(string $key, string $secret): ?ApiKey
    {
        $apiKey = ApiKey::where('key', $key)
            ->where('active', true)
            ->first();

        if (!$apiKey) {
            return null;
        }

        if ($apiKey->isExpired()) {
            return null;
        }

        // In production, use hash_equals for timing-attack resistance
        if (!hash_equals($apiKey->secret, hash('sha256', $secret))) {
            return null;
        }

        $apiKey->recordUsage();
        return $apiKey;
    }

    /**
     * Check rate limit for identifier
     */
    public function checkRateLimit(string $identifier, string $identifierType = 'ip'): bool
    {
        $rule = RateLimitRule::where('identifier_type', $identifierType)
            ->where('enabled', true)
            ->first();

        if (!$rule) {
            return true; // No limit configured
        }

        $throttle = ApiThrottle::where('identifier', $identifier)
            ->where('identifier_type', $identifierType)
            ->first();

        if (!$throttle || $throttle->isWindowExpired()) {
            // Create new throttle window
            ApiThrottle::create([
                'identifier' => $identifier,
                'identifier_type' => $identifierType,
                'request_count' => 1,
                'window_reset_at' => now()->addMinute(),
            ]);
            return true;
        }

        $currentCount = $throttle->incrementCount();
        
        return $currentCount <= $rule->requests_per_minute;
    }

    /**
     * Log API request
     */
    public function logApiRequest(
        $apiKeyId,
        $userId,
        string $endpoint,
        string $method,
        int $statusCode,
        int $responseTimeMs,
        array $requestData = [],
        array $responseData = [],
        string $errorMessage = null,
        string $userAgent = null
    ): ApiRequestLog {
        return ApiRequestLog::create([
            'api_key_id' => $apiKeyId,
            'user_id' => $userId,
            'endpoint' => $endpoint,
            'method' => $method,
            'ip_address' => request()->ip(),
            'status_code' => $statusCode,
            'response_time_ms' => $responseTimeMs,
            'request_data' => $requestData,
            'response_data' => $responseData,
            'error_message' => $errorMessage,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Create a webhook endpoint
     */
    public function createWebhook(
        $userId,
        string $name,
        string $url,
        array $events,
        array $headers = [],
        string $description = null
    ): Webhook {
        $secret = Str::random(32);

        return Webhook::create([
            'user_id' => $userId,
            'name' => $name,
            'url' => $url,
            'events' => $events,
            'secret' => $secret,
            'headers' => $headers,
            'description' => $description,
            'active' => true,
        ]);
    }

    /**
     * Trigger a webhook
     */
    public function triggerWebhook(Webhook $webhook, string $eventType, array $payload): void
    {
        // Find all webhooks that listen to this event
        $webhooks = Webhook::where('active', true)
            ->where(function ($query) use ($eventType) {
                $query->whereJsonContains('events', $eventType)
                    ->orWhereJsonContains('events', '*'); // Wildcard listener
            })
            ->get();

        foreach ($webhooks as $webhook) {
            $this->queueWebhookDelivery($webhook, $eventType, $payload);
        }
    }

    /**
     * Queue a webhook delivery
     */
    private function queueWebhookDelivery(Webhook $webhook, string $eventType, array $payload): WebhookDelivery
    {
        return WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'event_type' => $eventType,
            'payload' => $payload,
            'status' => 'pending',
        ]);
    }

    /**
     * Process pending webhook deliveries
     */
    public function processPendingDeliveries(): int
    {
        $deliveries = WebhookDelivery::pending()->get();
        $processed = 0;

        foreach ($deliveries as $delivery) {
            if ($this->sendWebhookDelivery($delivery)) {
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Send webhook delivery
     */
    private function sendWebhookDelivery(WebhookDelivery $delivery): bool
    {
        $webhook = $delivery->webhook;
        $payload = json_encode($delivery->payload);
        $signature = $webhook->getSignature($payload);

        try {
            $headers = $webhook->headers ?? [];
            $headers['Content-Type'] = 'application/json';
            $headers['X-Webhook-Signature'] = $signature;
            $headers['X-Event-Type'] = $delivery->event_type;
            $headers['X-Delivery-ID'] = $delivery->id;

            $response = Http::timeout($webhook->timeout_seconds)
                ->withHeaders($headers)
                ->post($webhook->url, $delivery->payload);

            if ($response->successful()) {
                $delivery->markDelivered($response->status(), $response->body());
                $webhook->recordTrigger();
                return true;
            } else {
                // Retry logic
                $delivery->markFailed("HTTP {$response->status()}: {$response->body()}");
                return false;
            }
        } catch (\Exception $e) {
            $delivery->markFailed("Exception: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get webhook statistics
     */
    public function getWebhookStats(Webhook $webhook): array
    {
        $deliveries = $webhook->deliveries();
        
        return [
            'total_deliveries' => $deliveries->count(),
            'successful' => $deliveries->where('status', 'delivered')->count(),
            'failed' => $deliveries->where('status', 'failed')->count(),
            'pending' => $deliveries->where('status', 'pending')->count(),
            'success_rate' => $deliveries->count() > 0
                ? ($deliveries->where('status', 'delivered')->count() / $deliveries->count()) * 100
                : 0,
            'average_response_time' => $deliveries->avg('response_time_ms'),
            'last_triggered' => $webhook->last_triggered_at,
        ];
    }

    /**
     * Get API usage statistics
     */
    public function getApiUsageStats($apiKeyId = null, int $days = 30): array
    {
        $query = ApiRequestLog::where('created_at', '>=', now()->subDays($days));
        
        if ($apiKeyId) {
            $query->where('api_key_id', $apiKeyId);
        }

        $logs = $query->get();

        return [
            'total_requests' => $logs->count(),
            'successful_requests' => $logs->where('status_code', '<', 400)->count(),
            'failed_requests' => $logs->where('status_code', '>=', 400)->count(),
            'average_response_time' => $logs->avg('response_time_ms'),
            'requests_by_endpoint' => $logs->groupBy('endpoint')->map->count(),
            'requests_by_method' => $logs->groupBy('method')->map->count(),
        ];
    }
}
