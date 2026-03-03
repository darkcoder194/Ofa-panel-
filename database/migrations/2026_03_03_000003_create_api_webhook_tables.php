<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // API Keys Management
        Schema::create('ofa_api_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('key')->unique();
            $table->string('secret')->unique();
            $table->text('description')->nullable();
            $table->json('permissions')->nullable(); // Array of allowed endpoints/actions
            $table->json('restricted_servers')->nullable(); // Array of server IDs access is limited to
            $table->timestamp('last_used_at')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'active']);
        });

        // API Rate Limiting Rules
        Schema::create('ofa_rate_limit_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('identifier_type'); // api_key, ip, user
            $table->integer('requests_per_minute')->default(60);
            $table->integer('requests_per_hour')->default(3600);
            $table->integer('requests_per_day')->default(86400);
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        // API Request Logs
        Schema::create('ofa_api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('api_key_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('endpoint');
            $table->string('method'); // GET, POST, PUT, DELETE, PATCH
            $table->string('ip_address');
            $table->integer('status_code');
            $table->unsignedBigInteger('response_time_ms')->default(0);
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index(['api_key_id', 'created_at']);
            $table->index(['endpoint', 'created_at']);
        });

        // Webhook Endpoints
        Schema::create('ofa_webhooks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('url');
            $table->json('events'); // Array of events to trigger on (server.created, server.deleted, etc)
            $table->string('secret')->nullable(); // For HMAC verification
            $table->integer('timeout_seconds')->default(30);
            $table->integer('max_retries')->default(3);
            $table->boolean('active')->default(true);
            $table->json('headers')->nullable(); // Custom headers
            $table->text('description')->nullable();
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('total_triggers')->default(0);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'active']);
        });

        // Webhook Delivery Queue
        Schema::create('ofa_webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('webhook_id');
            $table->string('event_type');
            $table->json('payload');
            $table->string('status')->default('pending'); // pending, delivered, failed, retry
            $table->integer('attempt_count')->default(0);
            $table->string('http_status_code')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();
            $table->foreign('webhook_id')->references('id')->on('ofa_webhooks')->onDelete('cascade');
            $table->index(['webhook_id', 'status', 'created_at']);
            $table->index(['status', 'next_retry_at']);
        });

        // API Throttle Cache (for rate limiting)
        Schema::create('ofa_api_throttles', function (Blueprint $table) {
            $table->id();
            $table->string('identifier'); // api_key, ip, user_id
            $table->string('identifier_type');
            $table->integer('request_count')->default(0);
            $table->timestamp('window_reset_at');
            $table->timestamps();
            $table->index(['identifier', 'identifier_type']);
        });

        // API Endpoint Documentation
        Schema::create('ofa_api_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('method');
            $table->string('path');
            $table->text('description')->nullable();
            $table->json('parameters')->nullable();
            $table->json('response_example')->nullable();
            $table->json('required_permissions')->nullable();
            $table->string('status')->default('active'); // active, deprecated, experimental
            $table->string('version')->default('v1');
            $table->timestamps();
            $table->unique(['method', 'path', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofa_api_endpoints');
        Schema::dropIfExists('ofa_api_throttles');
        Schema::dropIfExists('ofa_webhook_deliveries');
        Schema::dropIfExists('ofa_webhooks');
        Schema::dropIfExists('ofa_api_request_logs');
        Schema::dropIfExists('ofa_rate_limit_rules');
        Schema::dropIfExists('ofa_api_keys');
    }
};
