<?php

namespace DarkCoder\Ofa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerMetric extends Model
{
    protected $table = 'ofa_server_metrics';

    protected $fillable = [
        'server_id', 'cpu_usage', 'memory_usage', 'disk_usage',
        'network_in', 'network_out', 'player_count', 'ticks_per_second',
        'metadata', 'recorded_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function getHighestCpuInLastHour(): float
    {
        return $this->where('server_id', $this->server_id)
            ->where('recorded_at', '>=', now()->subHour())
            ->max('cpu_usage') ?? 0;
    }

    public function getAverageMetricsForPeriod(int $hours = 24): array
    {
        $metrics = $this->where('server_id', $this->server_id)
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->selectRaw('AVG(cpu_usage) as avg_cpu, AVG(memory_usage) as avg_memory, AVG(disk_usage) as avg_disk')
            ->first();

        return [
            'avg_cpu' => $metrics->avg_cpu ?? 0,
            'avg_memory' => $metrics->avg_memory ?? 0,
            'avg_disk' => $metrics->avg_disk ?? 0,
        ];
    }
}

class UserMetric extends Model
{
    protected $table = 'ofa_user_metrics';

    protected $fillable = [
        'user_id', 'logins_today', 'actions_today', 'servers_managed',
        'last_login', 'last_action', 'metadata'
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'last_action' => 'datetime',
        'metadata' => 'array',
    ];
}

class SystemHealth extends Model
{
    protected $table = 'ofa_system_health';
    public $timestamps = true;

    protected $fillable = [
        'overall_health', 'api_latency', 'database_latency',
        'total_servers', 'active_servers', 'average_cpu', 'average_memory',
        'alerts', 'warnings'
    ];

    protected $casts = [
        'alerts' => 'array',
        'warnings' => 'array',
    ];

    public static function getCurrentHealth(): self
    {
        return self::latest()->first() ?? new self();
    }

    public function isHealthy(): bool
    {
        return $this->overall_health >= 80;
    }

    public function getHealthPercentage(): string
    {
        return number_format($this->overall_health, 2) . '%';
    }
}

class PerformanceSnapshot extends Model
{
    protected $table = 'ofa_performance_snapshots';
    public $timestamps = true;

    protected $fillable = [
        'metric_type', 'data', 'snapshot_date'
    ];

    protected $casts = [
        'data' => 'array',
        'snapshot_date' => 'datetime',
    ];
}

class AlertRule extends Model
{
    protected $table = 'ofa_alert_rules';

    protected $fillable = [
        'name', 'alert_type', 'threshold', 'condition',
        'check_interval', 'enabled', 'notification_channel',
        'recipients', 'description'
    ];

    protected $casts = [
        'recipients' => 'array',
    ];

    public function triggers()
    {
        return $this->hasMany(AlertTrigger::class, 'alert_rule_id');
    }

    public function shouldTrigger($actualValue): bool
    {
        return match ($this->condition) {
            '>' => $actualValue > $this->threshold,
            '<' => $actualValue < $this->threshold,
            '>=' => $actualValue >= $this->threshold,
            '<=' => $actualValue <= $this->threshold,
            '=' => $actualValue == $this->threshold,
            default => false,
        };
    }
}

class AlertTrigger extends Model
{
    protected $table = 'ofa_alert_triggers';

    protected $fillable = [
        'alert_rule_id', 'server_id', 'severity', 'message',
        'actual_value', 'resolved', 'resolved_at'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function alertRule()
    {
        return $this->belongsTo(AlertRule::class, 'alert_rule_id');
    }

    public function resolve($notes = null): void
    {
        $this->update([
            'resolved' => true,
            'resolved_at' => now(),
        ]);
    }
}
