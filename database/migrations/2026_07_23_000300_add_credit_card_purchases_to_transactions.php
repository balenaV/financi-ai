<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->change();
            $table->string('payment_channel', 24)->default('account')->after('type');
            $table->foreignId('credit_card_id')->nullable()->after('destination_account_id')->constrained()->restrictOnDelete();
            $table->foreignId('credit_card_bill_id')->nullable()->after('credit_card_id')->constrained()->nullOnDelete();
            $table->index(['user_id', 'payment_channel', 'credit_card_id']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'payment_channel', 'credit_card_id']);
            $table->dropConstrainedForeignId('credit_card_bill_id');
            $table->dropConstrainedForeignId('credit_card_id');
            $table->dropColumn('payment_channel');
            $table->foreignId('account_id')->nullable(false)->change();
        });
    }
};
