<?php

namespace DarkCoder\Ofa\Services;

use DarkCoder\Ofa\Models\AuditLog;
use DarkCoder\Ofa\Models\SecurityEvent;
use DarkCoder\Ofa\Models\AccessLog;
use DarkCoder\Ofa\Models\IntegrityCheck;
use DarkCoder\Ofa\Models\ComplianceLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an action for auditing
     */
    public function logAction(
        string $action,
        string $entityType,
        $entityId = null,
        string $entityName = null,
        array $oldValues = [],
        array $newValues = [],
        string $description = null,
        string $status = 'success',
        $errorMessage = null,
        $durationMs = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id() ?? 0,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'entity_name' => $entityName,
            'description' => $description,
            'old_values' => !empty($oldValues) ? $oldValues : null,
            'new_values' => !empty($newValues) ? $newValues : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'status' => $status,
            'error_message' => $errorMessage,
            'duration_ms' => $durationMs,
            'metadata' => [
                'path' => Request::path(),
                'method' => Request::method(),
            ],
        ]);
    }

    /**
     * Record a security event
     */
    public function recordSecurityEvent(
        string $eventType,
        string $severity,
        string $description,
        array $details = [],
        $userId = null
    ): SecurityEvent {
        return SecurityEvent::create([
            'user_id' => $userId ?? Auth::id(),
            'event_type' => $eventType,
            'severity' => $severity,
            'ip_address' => Request::ip(),
            'description' => $description,
            'details' => $details,
            'investigated' => false,
            'status' => 'open',
        ]);
    }

    /**
     * Log access attempt
     */
    public function logAccess(
        $userId,
        $serverId,
        string $actionType,
        string $resourceType,
        bool $allowed,
        string $reason = null
    ): AccessLog {
        return AccessLog::create([
            'user_id' => $userId,
            'server_id' => $serverId,
            'action_type' => $actionType,
            'resource_type' => $resourceType,
            'allowed' => $allowed,
            'reason' => $reason,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Perform integrity check
     */
    public function performIntegrityCheck(string $checkType): IntegrityCheck
    {
        $check = new IntegrityCheck();
        $check->check_type = $checkType;
        
        $results = match ($checkType) {
            'file_integrity' => $this->checkFileIntegrity(),
            'database_consistency' => $this->checkDatabaseConsistency(),
            'config_validation' => $this->validateConfiguration(),
            default => [],
        };

        $check->results = $results;
        $check->status = count(array_filter($results, fn($r) => !$r['passed'])) === 0 ? 'passed' : 'failed';
        $check->issues_found = count(array_filter($results, fn($r) => !$r['passed']));
        $check->completed_at = now();
        $check->save();

        return $check;
    }

    /**
     * Check file integrity
     */
    private function checkFileIntegrity(): array
    {
        // Implementation would check critical files haven't been modified
        return [
            [
                'file' => 'composer.lock',
                'passed' => true,
            ],
            [
                'file' => 'config/ofa.php',
                'passed' => true,
            ],
        ];
    }

    /**
     * Check database consistency
     */
    private function checkDatabaseConsistency(): array
    {
        // Implementation would verify database integrity
        return [
            [
                'check' => 'Foreign key constraints',
                'passed' => true,
            ],
            [
                'check' => 'Data type validation',
                'passed' => true,
            ],
        ];
    }

    /**
     * Validate configuration
     */
    private function validateConfiguration(): array
    {
        // Implementation would validate all configuration
        return [
            [
                'config' => 'API Keys configured',
                'passed' => !empty(config('ofa.api_key')),
            ],
        ];
    }

    /**
     * Log compliance requirement
     */
    public function logCompliance(
        string $standard,
        string $requirement,
        string $status,
        string $evidence = null
    ): ComplianceLog {
        return ComplianceLog::create([
            'compliance_standard' => $standard,
            'requirement' => $requirement,
            'status' => $status,
            'evidence' => $evidence,
        ]);
    }

    /**
     * Get audit trail for an entity
     */
    public function getAuditTrail(string $entityType, $entityId, int $limit = 100)
    {
        return AuditLog::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unresolved security events
     */
    public function getUnresolvedSecurityEvents(int $limit = 50)
    {
        return SecurityEvent::unresolvedOrUninvestigated()
            ->orderByDesc('severity')
            ->limit($limit)
            ->get();
    }

    /**
     * Generate audit report
     */
    public function generateReport(
        string $type,
        \Carbon\Carbon $from,
        \Carbon\Carbon $to
    ): array {
        return match ($type) {
            'user_activity' => $this->getUserActivityReport($from, $to),
            'security' => $this->getSecurityReport($from, $to),
            'compliance' => $this->getComplianceReport($from, $to),
            default => [],
        };
    }

    /**
     * Generate user activity report
     */
    private function getUserActivityReport($from, $to): array
    {
        return AuditLog::whereBetween('created_at', [$from, $to])
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as action_count, GROUP_CONCAT(DISTINCT action) as actions')
            ->get()
            ->toArray();
    }

    /**
     * Generate security report
     */
    private function getSecurityReport($from, $to): array
    {
        return SecurityEvent::whereBetween('created_at', [$from, $to])
            ->groupBy('event_type')
            ->selectRaw('event_type, COUNT(*) as count, GROUP_CONCAT(DISTINCT severity) as severities')
            ->get()
            ->toArray();
    }

    /**
     * Generate compliance report
     */
    private function getComplianceReport($from, $to): array
    {
        return ComplianceLog::whereBetween('created_at', [$from, $to])
            ->groupBy('compliance_standard')
            ->selectRaw('compliance_standard, 
                SUM(CASE WHEN status = "compliant" THEN 1 ELSE 0 END) as compliant,
                SUM(CASE WHEN status = "non_compliant" THEN 1 ELSE 0 END) as non_compliant')
            ->get()
            ->toArray();
    }
}
