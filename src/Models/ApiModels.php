<?php

namespace DarkCoder\Ofa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $table = 'ofa_api_keys';

    protected $fillable = [
        'user_id', 'name', 'key', 'secret', 'description',
        'permissions', 'restricted_servers', 'last_used_at',
        'usage_count', 'expires_at', 'active'
    ];

    protected $casts = [
        'permissions' => 'array',
        'restricted_servers' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $hidden = ['secret'];

    public static function generateKey(): array
    {
        return [
            'key' => 'ofa_' . Str::random(32),
            'secret' => hash('sha256', Str::random(64)),
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function hasPermission($permission): bool
    {
        if (empty($this->permissions)) {
            return false;
        }
        return in_array($permission, $this->permissions) || in_array('*', $this->permissions);
    }

    public function hasServerAccess($serverId): bool
    {
        if (empty($this->restricted_servers)) {
            return true; // No restrictions
        }
        return in_array($serverId, $this->restricted_servers);
    }

    public function recordUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }
}

class RateLimitRule extends Model
{
    protected $table = 'ofa_rate_limit_rules';
    public $timestamps = true;

    protected $fillable = [
        'name', 'identifier_type', 'requests_per_minute',
        'requests_per_hour', 'requests_per_day', 'description', 'enabled'
    ];
}

class ApiRequestLog extends Model
{
    protected $table = 'ofa_api_request_logs';
    public $timestamps = true;

    protected $fillable = [
        'api_key_id', 'user_id', 'endpoint', 'method', 'ip_address',
        'status_code', 'response_time_ms', 'request_data', 'response_data',
        'error_message', 'user_agent'
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    public function scopeSlowRequests($query, $threshold = 1000)
    {
        return $query->where('response_time_ms', '>', $threshold);
    }

    public function scopeErrors($query)
    {
        return $query->where('status_code', '>=', 400);
    }
}

class Webhook extends Model
{
    protected $table = 'ofa_webhooks';

    protected $fillable = [
        'user_id', 'name', 'url', 'events', 'secret',
        'timeout_seconds', 'max_retries', 'active', 'headers',
        'description', 'last_triggered_at', 'total_triggers'
    ];

    protected $casts = [
        'events' => 'array',
        'headers' => 'array',
        'last_triggered_at' => 'datetime',
    ];

    public function deliveries()
    {
        return $this->hasMany(WebhookDelivery::class, 'webhook_id');
    }

    public function listensToEvent($eventType): bool
    {
        return in_array($eventType, $this->events ?? []);
    }

    public function recordTrigger(): void
    {
        $this->update([
            'last_triggered_at' => now(),
            'total_triggers' => $this->total_triggers + 1,
        ]);
    }

    public function getSignature(string $payload): string
    {
        if (!$this->secret) {
            return '';
        }
        return hash_hmac('sha256', $payload, $this->secret);
    }
}

class WebhookDelivery extends Model
{
    protected $table = 'ofa_webhook_deliveries';
    public $timestamps = true;

    protected $fillable = [
        'webhook_id', 'event_type', 'payload', 'status',
        'attempt_count', 'http_status_code', 'response_body',
        'error_message', 'delivered_at', 'next_retry_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'delivered_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    public function webhook()
    {
        return $this->belongsTo(Webhook::class, 'webhook_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')->orWhere('status', 'retry');
    }

    public function scopeReadyForRetry($query)
    {
        return $query->where('status', 'retry')
            ->where('next_retry_at', '<=', now())
            ->where('attempt_count', '<', $this->webhook()->max('max_retries'));
    }

    public function markDelivered($statusCode, $responseBody = null): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'http_status_code' => $statusCode,
            'response_body' => $responseBody,
        ]);
    }

    public function markFailed($errorMessage, $retryAfterSeconds = 300): void
    {
        $this->update([
            'error_message' => $errorMessage,
            'attempt_count' => $this->attempt_count + 1,
            'status' => $this->attempt_count < ($this->webhook()->max_retries ?? 3) ? 'retry' : 'failed',
            'next_retry_at' => now()->addSeconds($retryAfterSeconds),
        ]);
    }
}

class ApiThrottle extends Model
{
    protected $table = 'ofa_api_throttles';
    public $timestamps = true;

    protected $fillable = [
        'identifier', 'identifier_type', 'request_count', 'window_reset_at'
    ];

    protected $casts = [
        'window_reset_at' => 'datetime',
    ];

    public function incrementCount(): int
    {
        return $this->increment('request_count')->request_count;
    }

    public function isWindowExpired(): bool
    {
        return $this->window_reset_at ? $this->window_reset_at->isPast() : true;
    }
}

class ApiEndpoint extends Model
{
    protected $table = 'ofa_api_endpoints';
    public $timestamps = true;

    protected $fillable = [
        'name', 'method', 'path', 'description', 'parameters',
        'response_example', 'required_permissions', 'status', 'version'
    ];

    protected $casts = [
        'parameters' => 'array',
        'response_example' => 'array',
        'required_permissions' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVersion($query, $version)
    {
        return $query->where('version', $version);
    }
}
