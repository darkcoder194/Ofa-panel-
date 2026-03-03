<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Event Registry
        Schema::create('ofa_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name')->unique(); // server.created, server.started, etc
            $table->text('description')->nullable();
            $table->json('payload_structure')->nullable(); // Expected payload format
            $table->string('category'); // server, user, system, billing
            $table->boolean('is_system')->default(false); // Internal system event
            $table->timestamps();
            $table->index(['category', 'is_system']);
        });

        // Event Listeners
        Schema::create('ofa_event_listeners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('listener_type'); // webhook, queue_job, email, internal_callback
            $table->string('listener_target'); // URL, job class, email addr, callback class
            $table->json('conditions')->nullable(); // Array of conditions that must be met
            $table->boolean('active')->default(true);
            $table->integer('retry_count')->default(3);
            $table->integer('timeout_seconds')->default(30);
            $table->timestamps();
            $table->foreign('event_id')->references('id')->on('ofa_events')->onDelete('cascade');
        });

        // Event History
        Schema::create('ofa_event_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('triggered_by_user')->nullable();
            $table->unsignedBigInteger('related_server_id')->nullable();
            $table->json('payload')->nullable();
            $table->string('status')->default('success'); // success, failed, partial
            $table->integer('listeners_executed')->default(0);
            $table->integer('listeners_failed')->default(0);
            $table->json('execution_log')->nullable();
            $table->unsignedBigInteger('duration_ms')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->foreign('event_id')->references('id')->on('ofa_events')->onDelete('cascade');
            $table->index(['event_id', 'created_at']);
            $table->index(['triggered_by_user', 'created_at']);
        });

        // Real-time Notifications
        Schema::create('ofa_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('notification_type'); // alert, warning, info, success
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('channel')->default('in-app'); // in-app, email, sms, push
            $table->json('data')->nullable(); // Additional context data
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'read', 'created_at']);
        });

        // Notification Preferences
        Schema::create('ofa_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('notification_type');
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'notification_type']);
        });

        // Scheduled Events/Cron Jobs
        Schema::create('ofa_scheduled_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->string('cron_expression');
            $table->string('event_type'); // internal_function, webhook, plugin_hook
            $table->string('handler'); // Class@method or URL
            $table->json('parameters')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_executed_at')->nullable();
            $table->timestamp('next_execution_at')->nullable();
            $table->timestamps();
            $table->index(['enabled', 'next_execution_at']);
        });

        // Event Execution History (for scheduled events)
        Schema::create('ofa_scheduled_event_executions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheduled_event_id');
            $table->string('status'); // success, failed, timeout
            $table->text('output')->nullable();
            $table->unsignedBigInteger('duration_ms')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->foreign('scheduled_event_id')->references('id')->on('ofa_scheduled_events')->onDelete('cascade');
            $table->index(['scheduled_event_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofa_scheduled_event_executions');
        Schema::dropIfExists('ofa_scheduled_events');
        Schema::dropIfExists('ofa_notification_preferences');
        Schema::dropIfExists('ofa_notifications');
        Schema::dropIfExists('ofa_event_history');
        Schema::dropIfExists('ofa_event_listeners');
        Schema::dropIfExists('ofa_events');
    }
};
