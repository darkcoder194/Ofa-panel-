<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Server Performance Metrics
        Schema::create('ofa_server_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id');
            $table->float('cpu_usage')->default(0);
            $table->float('memory_usage')->default(0);
            $table->float('disk_usage')->default(0);
            $table->integer('network_in')->default(0);
            $table->integer('network_out')->default(0);
            $table->integer('player_count')->default(0);
            $table->integer('ticks_per_second')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->index(['server_id', 'recorded_at']);
        });

        // User Activity Metrics
        Schema::create('ofa_user_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('logins_today')->default(0);
            $table->integer('actions_today')->default(0);
            $table->integer('servers_managed')->default(0);
            $table->timestamp('last_login')->nullable();
            $table->timestamp('last_action')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        // System Health Dashboard Data
        Schema::create('ofa_system_health', function (Blueprint $table) {
            $table->id();
            $table->float('overall_health')->default(100);
            $table->float('api_latency')->default(0);
            $table->float('database_latency')->default(0);
            $table->integer('total_servers')->default(0);
            $table->integer('active_servers')->default(0);
            $table->float('average_cpu')->default(0);
            $table->float('average_memory')->default(0);
            $table->json('alerts')->nullable();
            $table->json('warnings')->nullable();
            $table->timestamps();
        });

        // Performance Snapshots (Historical Data)
        Schema::create('ofa_performance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('metric_type'); // cpu, memory, disk, network, api_calls
            $table->json('data');
            $table->timestamp('snapshot_date');
            $table->timestamps();
            $table->index(['metric_type', 'snapshot_date']);
        });

        // Alert Rules & Thresholds
        Schema::create('ofa_alert_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alert_type'); // cpu, memory, disk, bandwidth, uptime
            $table->float('threshold');
            $table->string('condition'); // >, <, =, >=, <=
            $table->integer('check_interval')->default(5); // minutes
            $table->boolean('enabled')->default(true);
            $table->string('notification_channel')->default('email'); // email, webhook, sms
            $table->json('recipients')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Triggered Alerts History
        Schema::create('ofa_alert_triggers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alert_rule_id');
            $table->unsignedBigInteger('server_id')->nullable();
            $table->string('severity'); // critical, warning, info
            $table->string('message');
            $table->float('actual_value')->nullable();
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->foreign('alert_rule_id')->references('id')->on('ofa_alert_rules')->onDelete('cascade');
            $table->index(['alert_rule_id', 'server_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofa_alert_triggers');
        Schema::dropIfExists('ofa_alert_rules');
        Schema::dropIfExists('ofa_performance_snapshots');
        Schema::dropIfExists('ofa_system_health');
        Schema::dropIfExists('ofa_user_metrics');
        Schema::dropIfExists('ofa_server_metrics');
    }
};
