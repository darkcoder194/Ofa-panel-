<?php

namespace DarkCoder\Ofa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class AuditLog extends Model
{
    protected $table = 'ofa_audit_logs';

    protected $fillable = [
        'user_id', 'action', 'entity_type', 'entity_id', 'entity_name',
        'description', 'old_values', 'new_values', 'ip_address', 'user_agent',
        'status', 'error_message', 'duration_ms', 'metadata'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForEntity($query, $entityType, $entityId = null)
    {
        $query->where('entity_type', $entityType);
        if ($entityId) {
            $query->where('entity_id', $entityId);
        }
        return $query;
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function getChangesAttribute(): array
    {
        $changes = [];
        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? null;
                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }
        }
        return $changes;
    }
}

class SecurityEvent extends Model
{
    protected $table = 'ofa_security_events';

    protected $fillable = [
        'user_id', 'event_type', 'severity', 'ip_address',
        'description', 'details', 'investigated', 'investigation_notes',
        'investigated_at', 'status'
    ];

    protected $casts = [
        'details' => 'array',
        'investigated_at' => 'datetime',
    ];

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeUnresolved($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeUninvestigated($query)
    {
        return $query->where('investigated', false);
    }

    public function investigate($notes): void
    {
        $this->update([
            'investigated' => true,
            'investigation_notes' => $notes,
            'investigated_at' => now(),
            'status' => 'resolved',
        ]);
    }
}

class AccessLog extends Model
{
    protected $table = 'ofa_access_logs';
    public $timestamps = true;

    protected $fillable = [
        'user_id', 'server_id', 'action_type', 'resource_type',
        'allowed', 'reason', 'ip_address'
    ];

    public function scopeDenied($query)
    {
        return $query->where('allowed', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForResource($query, $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }
}

class IntegrityCheck extends Model
{
    protected $table = 'ofa_integrity_checks';
    public $timestamps = true;

    protected $fillable = [
        'check_type', 'status', 'results', 'findings',
        'recommendations', 'issues_found', 'completed_at'
    ];

    protected $casts = [
        'results' => 'array',
        'completed_at' => 'datetime',
    ];

    public function scopeByType($query, $type)
    {
        return $query->where('check_type', $type);
    }

    public function passedCheck(): bool
    {
        return $this->status === 'passed' && $this->issues_found === 0;
    }
}

class ComplianceLog extends Model
{
    protected $table = 'ofa_compliance_logs';
    public $timestamps = true;

    protected $fillable = [
        'compliance_standard', 'requirement', 'status',
        'evidence', 'remediation', 'remediated_by', 'remediated_at'
    ];

    protected $casts = [
        'remediated_at' => 'datetime',
    ];

    public function scopeByStandard($query, $standard)
    {
        return $query->where('compliance_standard', $standard);
    }

    public function scopeNonCompliant($query)
    {
        return $query->where('status', 'non_compliant');
    }

    public function remediate($notes, $userId): void
    {
        $this->update([
            'status' => 'compliant',
            'remediation' => $notes,
            'remediated_by' => $userId,
            'remediated_at' => now(),
        ]);
    }
}
