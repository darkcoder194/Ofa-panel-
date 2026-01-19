<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Addons tables
        Schema::create('ofa_subdomains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id');
            $table->string('subdomain')->unique();
            $table->string('target');
            $table->boolean('cloudflare_enabled')->default(false);
            $table->string('dns_type')->default('A');
            $table->string('cloudflare_record_id')->nullable();
            $table->timestamps();
        });

        Schema::create('ofa_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->string('category');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'waiting', 'resolved', 'closed'])->default('open');
            $table->timestamps();
        });

        Schema::create('ofa_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('ofa_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->string('attachments')->nullable();
            $table->timestamps();
        });

        Schema::create('ofa_reverse_proxies', function (Blueprint $table) {
            $table->id();
            $table->string('subdomain')->unique();
            $table->string('target_url');
            $table->boolean('ssl_enabled')->default(true);
            $table->boolean('cache_enabled')->default(false);
            $table->text('nginx_config')->nullable();
            $table->enum('status', ['active', 'inactive', 'error'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofa_reverse_proxies');
        Schema::dropIfExists('ofa_ticket_replies');
        Schema::dropIfExists('ofa_tickets');
        Schema::dropIfExists('ofa_subdomains');
    }
};
