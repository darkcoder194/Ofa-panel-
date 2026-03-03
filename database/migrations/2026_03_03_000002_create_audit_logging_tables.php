<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Comprehensive Audit Logs
        Schema::create('ofa_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action'); // create, read, update, delete, execute
            $table->string('entity_type'); // server, database, user, setting
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_name')->nullable();
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('status')->default('success'); // success, failed
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('duration_ms')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'action', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });

        // Security Events
        Schema::create('ofa_security_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event_type'); // failed_login, brute_force, suspicious_activity, permission_change
            $table->string('severity'); // low, medium, high, critical
            $table->string('ip_address')->nullable();
            $table->text('description')->nullable();
            $table->json('details')->nullable();
            $table->boolean('investigated')->default(false);
            $table->text('investigation_notes')->nullable();
            $table->timestamp('investigated_at')->nullable();
            $table->string('status')->default('open'); // open, resolved, dismissed
            $table->timestamps();
            $table->index(['user_id', 'event_type', 'created_at']);
            $table->index(['severity', 'investigated']);
        });

        // Access Control Logs
        Schema::create('ofa_access_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('server_id')->nullable();
            $table->string('action_type'); // access_granted, access_denied, permission_requested
            $table->string('resource_type'); // console, files, database, backup
            $table->boolean('allowed')->default(true);
            $table->string('reason')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'allowed']);
            $table->index(['server_id', 'created_at']);
        });

        // Data Integrity Checks
        Schema::create('ofa_integrity_checks', function (Blueprint $table) {
            $table->id();
            $table->string('check_type'); // file_integrity, database_consistency, config_validation
            $table->string('status'); // passed, failed, warning
            $table->json('results')->nullable();
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->unsignedBigInteger('issues_found')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['check_type', 'status']);
        });

        // Compliance & Regulatory Logs
        Schema::create('ofa_compliance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('compliance_standard'); // gdpr, hipaa, ccpa, sox
            $table->string('requirement');
            $table->string('status'); // compliant, non_compliant, pending_review
            $table->text('evidence')->nullable();
            $table->text('remediation')->nullable();
            $table->unsignedBigInteger('remediated_by')->nullable();
            $table->timestamp('remediated_at')->nullable();
            $table->timestamps();
            $table->index(['compliance_standard', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofa_compliance_logs');
        Schema::dropIfExists('ofa_integrity_checks');
        Schema::dropIfExists('ofa_access_logs');
        Schema::dropIfExists('ofa_security_events');
        Schema::dropIfExists('ofa_audit_logs');
    }
};
