<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('currency', 3)->default('BRL');
            $table->string('timezone')->default('America/Sao_Paulo');
            $table->unsignedTinyInteger('financial_month_start_day')->default(1);
            $table->string('view_preference')->default('comfortable');
            $table->boolean('hide_values')->default(false);
            $table->boolean('confirm_deletion')->default(true);
            $table->timestamps();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 32);
            $table->string('institution')->nullable();
            $table->decimal('initial_balance', 19, 2)->default(0);
            $table->date('initial_balance_date');
            $table->string('color', 9)->default('#534ab7');
            $table->string('icon', 32)->default('wallet');
            $table->string('currency', 3)->default('BRL');
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'active']);
            $table->index(['user_id', 'type']);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('type', 16);
            $table->string('color', 9)->default('#64748b');
            $table->string('icon', 32)->default('tag');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'type', 'name']);
            $table->index(['user_id', 'type', 'active']);
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->foreignId('destination_account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 16);
            $table->string('description');
            $table->decimal('amount', 19, 2);
            $table->date('competence_date');
            $table->date('due_date')->nullable();
            $table->date('paid_at')->nullable();
            $table->string('status', 16)->default('planned');
            $table->text('notes')->nullable();
            $table->uuid('installment_group_id')->nullable();
            $table->unsignedSmallInteger('installment_number')->nullable();
            $table->unsignedSmallInteger('installment_total')->nullable();
            $table->uuid('recurrence_group_id')->nullable();
            $table->string('source_type', 32)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'competence_date']);
            $table->index(['user_id', 'status', 'due_date']);
            $table->index(['user_id', 'account_id', 'status']);
            $table->index(['installment_group_id', 'installment_number']);
            $table->index(['recurrence_group_id', 'competence_date']);
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('creditor');
            $table->text('description')->nullable();
            $table->decimal('original_amount', 19, 2);
            $table->decimal('expected_total_amount', 19, 2);
            $table->decimal('interest_rate', 9, 4)->nullable();
            $table->date('started_at');
            $table->date('due_date')->nullable();
            $table->unsignedSmallInteger('installment_count')->default(1);
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_date']);
        });

        Schema::create('debt_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('debt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('number');
            $table->decimal('amount', 19, 2);
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->string('status', 16)->default('pending');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['debt_id', 'number']);
            $table->index(['user_id', 'status', 'due_date']);
        });

        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 32);
            $table->string('institution');
            $table->string('ticker')->nullable();
            $table->decimal('quantity', 24, 8)->nullable();
            $table->decimal('invested_amount', 19, 2)->default(0);
            $table->decimal('current_amount', 19, 2)->default(0);
            $table->date('last_updated_at');
            $table->string('liquidity')->nullable();
            $table->date('maturity_date')->nullable();
            $table->string('status', 16)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'type', 'status']);
            $table->index(['user_id', 'institution']);
        });

        Schema::create('investment_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('investment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 24);
            $table->decimal('amount', 19, 2);
            $table->decimal('quantity', 24, 8)->nullable();
            $table->date('operation_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'operation_date']);
            $table->index(['investment_id', 'type']);
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('limit_amount', 19, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'category_id', 'month', 'year']);
            $table->index(['user_id', 'year', 'month']);
        });

        Schema::create('financial_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('target_amount', 19, 2);
            $table->decimal('current_amount', 19, 2)->default(0);
            $table->date('deadline')->nullable();
            $table->string('color', 9)->default('#1d9e75');
            $table->string('status', 16)->default('active');
            $table->boolean('use_account_balance')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'deadline']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_goals');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('investment_operations');
        Schema::dropIfExists('investments');
        Schema::dropIfExists('debt_installments');
        Schema::dropIfExists('debts');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('user_settings');
    }
};
