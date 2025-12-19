<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ofa_server_actions', function (Blueprint $table) {
            $table->id();
            $table->uuid('server_uuid');
            $table->string('action_type'); // version|egg
            $table->json('payload')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofa_server_actions');
    }
};