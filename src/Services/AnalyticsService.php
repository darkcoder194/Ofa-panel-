<?php

namespace DarkCoder\Ofa\Services;

use DarkCoder\Ofa\Models\ServerMetric;
use DarkCoder\Ofa\Models\UserMetric;
use DarkCoder\Ofa\Models\SystemHealth;
use DarkCoder\Ofa\Models\AlertRule;
use DarkCoder\Ofa\Models\AlertTrigger;
use Illuminate\Support\Collection;

class AnalyticsService
{
    /**
     * Record a server metric
     */
    public function recordMetric(
        $serverId,
        $cpuUsage,
        $memoryUsage,
        $diskUsage,
        $networkIn = 0,
        $networkOut = 0,
        $playerCount = null,
        $tpsInfo = null
    ): ServerMetric {
        return ServerMetric::create([
            'server_id' => $serverId,
            'cpu_usage' => min($cpuUsage, 100),
            'memory_usage' => min($memoryUsage, 100),
            'disk_usage' => min($diskUsage, 100),
            'network_in' => $networkIn,
            'network_out' => $networkOut,
            'player_count' => $playerCount ?? 0,
            'ticks_per_second' => $tpsInfo,
            'recorded_at' => now(),
        ]);
    }

    /**
     * Get server performance for the last X hours
     */
    public function getServerPerformance($serverId, $hours = 24): array
    {
        $metrics = ServerMetric::where('server_id', $serverId)
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at')
            ->get();

        return [
            'cpu' => $metrics->pluck('cpu_usage')->toArray(),
            'memory' => $metrics->pluck('memory_usage')->toArray(),
            'disk' => $metrics->pluck('disk_usage')->toArray(),
            'timestamps' => $metrics->pluck('recorded_at')->toArray(),
            'average_cpu' => $metrics->avg('cpu_usage'),
            'average_memory' => $metrics->avg('memory_usage'),
            'peak_cpu' => $metrics->max('cpu_usage'),
            'peak_memory' => $metrics->max('memory_usage'),
        ];
    }

    /**
     * Update system health status
     */
    public function updateSystemHealth(): SystemHealth
    {
        $totalServers = getActiveServersCount(); // Helper function to get from Pterodactyl API
        $activeServers = getRunningServersCount();
        $avgCpu = ServerMetric::where('recorded_at', '>=', now()->subHour())->avg('cpu_usage');
        $avgMemory = ServerMetric::where('recorded_at', '>=', now()->subHour())->avg('memory_usage');

        $health = new SystemHealth();
        $health->total_servers = $totalServers;
        $health->active_servers = $activeServers;
        $health->average_cpu = $avgCpu ?? 0;
        $health->average_memory = $avgMemory ?? 0;
        $health->overall_health = $this->calculateSystemHealthScore($avgCpu, $avgMemory);
        $health->save();

        return $health;
    }

    /**
     * Calculate overall system health score (0-100)
     */
    private function calculateSystemHealthScore($cpuUsage, $memoryUsage): float
    {
        $cpuScore = max(0, 100 - ($cpuUsage * 1.2));
        $memoryScore = max(0, 100 - ($memoryUsage * 1.2));
        
        return round(($cpuScore + $memoryScore) / 2, 2);
    }

    /**
     * Check alert rules and trigger if needed
     */
    public function checkAlertRules(): void
    {
        $rules = AlertRule::where('enabled', true)->get();

        foreach ($rules as $rule) {
            $this->evaluateRule($rule);
        }
    }

    /**
     * Evaluate a single alert rule
     */
    private function evaluateRule(AlertRule $rule): void
    {
        // Get the current metric value based on alert type
        $currentValue = match ($rule->alert_type) {
            'cpu' => ServerMetric::latest('recorded_at')->avg('cpu_usage'),
            'memory' => ServerMetric::latest('recorded_at')->avg('memory_usage'),
            'disk' => ServerMetric::latest('recorded_at')->avg('disk_usage'),
            default => 0,
        };

        if ($rule->shouldTrigger($currentValue)) {
            // Check if alert was already triggered recently to avoid spamming
            $recentTrigger = $rule->triggers()
                ->where('resolved', false)
                ->latest()
                ->first();

            if (!$recentTrigger || $recentTrigger->created_at->diffInMinutes() >= $rule->check_interval) {
                $this->triggerAlert($rule, $currentValue);
            }
        }
    }

    /**
     * Trigger an alert
     */
    private function triggerAlert(AlertRule $rule, $actualValue): AlertTrigger
    {
        return AlertTrigger::create([
            'alert_rule_id' => $rule->id,
            'severity' => $this->determineSeverity($rule, $actualValue),
            'message' => "{$rule->name}: Value {$actualValue} triggers {$rule->condition} {$rule->threshold}",
            'actual_value' => $actualValue,
            'resolved' => false,
        ]);
    }

    /**
     * Determine severity level based on threshold
     */
    private function determineSeverity(AlertRule $rule, $value): string
    {
        $deviation = abs($value - $rule->threshold);
        
        if ($deviation > 30) {
            return 'critical';
        }
        if ($deviation > 15) {
            return 'warning';
        }
        return 'info';
    }

    /**
     * Get analytics dashboard data
     */
    public function getDashboardData(): array
    {
        $health = SystemHealth::getCurrentHealth();
        $recentErrors = AlertTrigger::where('resolved', false)
            ->where('severity', 'critical')
            ->latest()
            ->limit(5)
            ->get();

        return [
            'health' => $health,
            'recent_errors' => $recentErrors,
            'server_performance' => $this->getServerPerformance('all', 24),
            'top_servers' => $this->getTopCpuConsumers(5),
        ];
    }

    /**
     * Get top CPU consuming servers
     */
    public function getTopCpuConsumers(int $limit = 5): Collection
    {
        return ServerMetric::where('recorded_at', '>=', now()->subHour())
            ->selectRaw('server_id, AVG(cpu_usage) as avg_cpu')
            ->groupBy('server_id')
            ->orderByDesc('avg_cpu')
            ->limit($limit)
            ->get();
    }
}
