<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->string('theme', 16)->default('light')->after('view_preference');
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->string('kind', 24)->default('loan')->after('creditor');
            $table->index(['user_id', 'kind', 'status']);
        });

        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('issuer');
            $table->char('last_four', 4)->nullable();
            $table->decimal('credit_limit', 19, 2)->default(0);
            $table->unsignedTinyInteger('closing_day');
            $table->unsignedTinyInteger('due_day');
            $table->string('color', 9)->default('#534ab7');
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'active']);
            $table->index(['user_id', 'issuer']);
        });

        Schema::create('credit_card_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('credit_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->date('reference_month');
            $table->decimal('total_amount', 19, 2);
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->string('status', 16)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['credit_card_id', 'reference_month']);
            $table->index(['user_id', 'status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_card_bills');
        Schema::dropIfExists('credit_cards');

        Schema::table('debts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'kind', 'status']);
            $table->dropColumn('kind');
        });

        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn('theme');
        });
    }
};
