<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin;

use DarkCoder\Ofa\Services\AnalyticsService;
use DarkCoder\Ofa\Services\AuditService;
use DarkCoder\Ofa\Models\SystemHealth;
use DarkCoder\Ofa\Models\AlertTrigger;
use Illuminate\Http\Request;

class AnalyticsDashboardController
{
    protected $analyticsService;
    protected $auditService;

    public function __construct(AnalyticsService $analyticsService, AuditService $auditService)
    {
        $this->analyticsService = $analyticsService;
        $this->auditService = $auditService;
    }

    /**
     * Get main analytics dashboard
     */
    public function index()
    {
        $dashboardData = $this->analyticsService->getDashboardData();
        $recentAlerts = AlertTrigger::latest()->limit(10)->get();

        return response()->json([
            'health' => $dashboardData['health'],
            'recent_errors' => $dashboardData['recent_errors'],
            'server_performance' => $dashboardData['server_performance'],
            'top_servers' => $dashboardData['top_servers'],
            'recent_alerts' => $recentAlerts,
        ]);
    }

    /**
     * Get server performance details
     */
    public function serverPerformance($serverId)
    {
        $performance24h = $this->analyticsService->getServerPerformance($serverId, 24);
        $performance7d = $this->analyticsService->getServerPerformance($serverId, 168);

        return response()->json([
            'performance_24h' => $performance24h,
            'performance_7d' => $performance7d,
        ]);
    }

    /**
     * Get system health status
     */
    public function systemHealth()
    {
        $health = SystemHealth::latest()->first();
        
        return response()->json([
            'health' => $health,
            'status' => $health->isHealthy() ? 'healthy' : 'warning',
        ]);
    }

    /**
     * Get alert history
     */
    public function alertHistory(Request $request)
    {
        $query = AlertTrigger::query();

        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->has('resolved')) {
            $query->where('resolved', $request->boolean('resolved'));
        }

        $alerts = $query->latest()->paginate(50);

        return response()->json($alerts);
    }

    /**
     * Resolve an alert
     */
    public function resolveAlert(AlertTrigger $alert)
    {
        $alert->resolve();

        return response()->json([
            'message' => 'Alert resolved successfully',
            'alert' => $alert,
        ]);
    }

    /**
     * Get audit logs
     */
    public function auditLogs(Request $request)
    {
        $query = \DarkCoder\Ofa\Models\AuditLog::query();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->latest()->paginate(100);

        return response()->json($logs);
    }

    /**
     * Get security events
     */
    public function securityEvents(Request $request)
    {
        $query = \DarkCoder\Ofa\Models\SecurityEvent::query();

        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->boolean('unresolved')) {
            $query->where('status', 'open');
        }

        $events = $query->latest()->paginate(50);

        return response()->json($events);
    }

    /**
     * Investigate security event
     */
    public function investigateSecurityEvent(Request $request, \DarkCoder\Ofa\Models\SecurityEvent $event)
    {
        $validated = $request->validate([
            'investigation_notes' => 'required|string',
        ]);

        $event->investigate($validated['investigation_notes']);

        return response()->json([
            'message' => 'Security event investigated',
            'event' => $event,
        ]);
    }

    /**
     * Generate analytics report
     */
    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:performance,security,compliance,audit',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after:from_date',
        ]);

        $report = $this->auditService->generateReport(
            $validated['report_type'],
            \Carbon\Carbon::parse($validated['from_date']),
            \Carbon\Carbon::parse($validated['to_date'])
        );

        return response()->json([
            'type' => $validated['report_type'],
            'period' => [
                'from' => $validated['from_date'],
                'to' => $validated['to_date'],
            ],
            'data' => $report,
        ]);
    }
}
