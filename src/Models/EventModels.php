<?php

namespace DarkCoder\Ofa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfaEvent extends Model
{
    protected $table = 'ofa_events';

    protected $fillable = [
        'event_name', 'description', 'payload_structure', 'category', 'is_system'
    ];

    protected $casts = [
        'payload_structure' => 'array',
    ];

    public function listeners()
    {
        return $this->hasMany(EventListener::class, 'event_id');
    }

    public function history()
    {
        return $this->hasMany(EventHistory::class, 'event_id');
    }

    public function activeListeners()
    {
        return $this->listeners()->where('active', true);
    }
}

class EventListener extends Model
{
    protected $table = 'ofa_event_listeners';
    public $timestamps = true;

    protected $fillable = [
        'event_id', 'listener_type', 'listener_target', 'conditions',
        'active', 'retry_count', 'timeout_seconds'
    ];

    protected $casts = [
        'conditions' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(OfaEvent::class, 'event_id');
    }
}

class EventHistory extends Model
{
    protected $table = 'ofa_event_history';
    public $timestamps = true;

    protected $fillable = [
        'event_id', 'triggered_by_user', 'related_server_id', 'payload',
        'status', 'listeners_executed', 'listeners_failed', 'execution_log',
        'duration_ms', 'error_message'
    ];

    protected $casts = [
        'payload' => 'array',
        'execution_log' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(OfaEvent::class, 'event_id');
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function getSummary(): array
    {
        return [
            'total_listeners' => $this->listeners_executed + $this->listeners_failed,
            'executed' => $this->listeners_executed,
            'failed' => $this->listeners_failed,
            'success_rate' => $this->listeners_executed > 0
                ? ($this->listeners_executed / ($this->listeners_executed + $this->listeners_failed)) * 100
                : 0,
        ];
    }
}

class Notification extends Model
{
    protected $table = 'ofa_notifications';

    protected $fillable = [
        'user_id', 'notification_type', 'title', 'message',
        'channel', 'data', 'read', 'read_at', 'archived', 'archived_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    public function markAsRead(): void
    {
        $this->update([
            'read' => true,
            'read_at' => now(),
        ]);
    }

    public function archive(): void
    {
        $this->update([
            'archived' => true,
            'archived_at' => now(),
        ]);
    }
}

class NotificationPreference extends Model
{
    protected $table = 'ofa_notification_preferences';
    public $timestamps = true;

    protected $fillable = [
        'user_id', 'notification_type', 'email_enabled',
        'sms_enabled', 'in_app_enabled', 'push_enabled'
    ];

    public function isEnabledForChannel($channel): bool
    {
        return match ($channel) {
            'email' => $this->email_enabled,
            'sms' => $this->sms_enabled,
            'in_app' => $this->in_app_enabled,
            'push' => $this->push_enabled,
            default => false,
        };
    }
}

class ScheduledEvent extends Model
{
    protected $table = 'ofa_scheduled_events';
    public $timestamps = true;

    protected $fillable = [
        'event_name', 'cron_expression', 'event_type', 'handler',
        'parameters', 'enabled', 'last_executed_at', 'next_execution_at'
    ];

    protected $casts = [
        'parameters' => 'array',
        'last_executed_at' => 'datetime',
        'next_execution_at' => 'datetime',
    ];

    public function executions()
    {
        return $this->hasMany(ScheduledEventExecution::class, 'scheduled_event_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeDue($query)
    {
        return $query->where('next_execution_at', '<=', now());
    }

    public function recordExecution($status, $output = null, $duration = 0, $error = null): void
    {
        $this->executions()->create([
            'status' => $status,
            'output' => $output,
            'duration_ms' => $duration,
            'error_message' => $error,
        ]);

        $this->update(['last_executed_at' => now()]);
    }

    public function getLastExecution()
    {
        return $this->executions()->latest()->first();
    }

    public function getSuccessRate(): float
    {
        $total = $this->executions()->count();
        if ($total === 0) {
            return 0;
        }
        $successful = $this->executions()->where('status', 'success')->count();
        return ($successful / $total) * 100;
    }
}

class ScheduledEventExecution extends Model
{
    protected $table = 'ofa_scheduled_event_executions';
    public $timestamps = true;

    protected $fillable = [
        'scheduled_event_id', 'status', 'output', 'duration_ms', 'error_message'
    ];

    public function scheduledEvent()
    {
        return $this->belongsTo(ScheduledEvent::class, 'scheduled_event_id');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
