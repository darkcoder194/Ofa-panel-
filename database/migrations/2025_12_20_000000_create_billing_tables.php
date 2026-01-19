<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plans table
        Schema::create('ofa_billing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('cpu');
            $table->integer('memory');
            $table->integer('disk');
            $table->decimal('price', 10, 2);
            $table->enum('billing_period', ['monthly', 'yearly']);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Plan to Node pivot
        Schema::create('ofa_plan_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('ofa_billing_plans')->cascadeOnDelete();
            $table->unsignedBigInteger('node_id');
            $table->timestamps();
        });

        // Orders table
        Schema::create('ofa_billing_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('ofa_billing_plans');
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Invoices table
        Schema::create('ofa_billing_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('ofa_billing_orders')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->timestamp('paid_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        // Wallets table
        Schema::create('ofa_billing_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->timestamps();
        });

        // Wallet Transactions
        Schema::create('ofa_billing_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('ofa_billing_wallets')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['credit', 'debit']);
            $table->string('description');
            $table->string('reference_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofa_billing_wallet_transactions');
        Schema::dropIfExists('ofa_billing_wallets');
        Schema::dropIfExists('ofa_billing_invoices');
        Schema::dropIfExists('ofa_billing_orders');
        Schema::dropIfExists('ofa_plan_nodes');
        Schema::dropIfExists('ofa_billing_plans');
    }
};
