<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Registered Plugins
        Schema::create('ofa_plugins', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique(); // vendor/plugin-name
            $table->string('name');
            $table->string('version');
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->string('license')->nullable();
            $table->text('repository')->nullable();
            $table->json('requirements')->nullable(); // PHP version, Laravel version, etc
            $table->json('permissions_required')->nullable(); // Required permissions
            $table->string('main_class'); // Main plugin class
            $table->string('path'); // Plugin directory path
            $table->boolean('enabled')->default(false);
            $table->boolean('active')->default(false);
            $table->timestamp('activated_at')->nullable();
            $table->string('status')->default('inactive'); // inactive, active, error, maintenance
            $table->text('last_error')->nullable();
            $table->json('config')->nullable(); // Plugin configuration
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['enabled', 'active']);
        });

        // Plugin Dependencies
        Schema::create('ofa_plugin_dependencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plugin_id');
            $table->string('dependency_identifier'); // vendor/plugin-name
            $table->string('dependency_version')->nullable(); // ^1.0, >=2.0, etc
            $table->string('dependency_type')->default('require'); // require, suggest, conflict
            $table->timestamps();
            $table->foreign('plugin_id')->references('id')->on('ofa_plugins')->onDelete('cascade');
        });

        // Plugin Hooks Registry
        Schema::create('ofa_plugin_hooks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plugin_id');
            $table->string('hook_name'); // before_server_start, after_backup_complete, etc
            $table->string('callback_class');
            $table->string('callback_method');
            $table->integer('priority')->default(10);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->foreign('plugin_id')->references('id')->on('ofa_plugins')->onDelete('cascade');
            $table->index(['hook_name', 'priority']);
        });

        // Plugin Settings Store
        Schema::create('ofa_plugin_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plugin_id');
            $table->string('key');
            $table->longText('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json, array
            $table->boolean('user_configurable')->default(true);
            $table->timestamps();
            $table->foreign('plugin_id')->references('id')->on('ofa_plugins')->onDelete('cascade');
            $table->unique(['plugin_id', 'key']);
        });

        // Plugin Event Logs
        Schema::create('ofa_plugin_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plugin_id');
            $table->string('level'); // debug, info, warning, error, critical
            $table->text('message');
            $table->json('context')->nullable();
            $table->string('event_type')->nullable(); // activation, execution, error
            $table->timestamps();
            $table->foreign('plugin_id')->references('id')->on('ofa_plugins')->onDelete('cascade');
            $table->index(['plugin_id', 'level', 'created_at']);
        });

        // Plugin Permissions
        Schema::create('ofa_plugin_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plugin_id');
            $table->string('permission_name'); // plugin.plugin_name.action
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('plugin_id')->references('id')->on('ofa_plugins')->onDelete('cascade');
            $table->unique(['plugin_id', 'permission_name']);
        });

        // Plugin Marketplace (Registry)
        Schema::create('ofa_plugin_marketplace', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique();
            $table->string('name');
            $table->string('version');
            $table->text('description')->nullable();
            $table->text('long_description')->nullable();
            $table->string('author')->nullable();
            $table->json('tags')->nullable();
            $table->string('download_url');
            $table->json('changelog')->nullable();
            $table->integer('download_count')->default(0);
            $table->float('rating')->default(0);
            $table->integer('reviews_count')->default(0);
            $table->boolean('verified')->default(false);
            $table->boolean('featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofa_plugin_marketplace');
        Schema::dropIfExists('ofa_plugin_permissions');
        Schema::dropIfExists('ofa_plugin_logs');
        Schema::dropIfExists('ofa_plugin_settings');
        Schema::dropIfExists('ofa_plugin_hooks');
        Schema::dropIfExists('ofa_plugin_dependencies');
        Schema::dropIfExists('ofa_plugins');
    }
};
