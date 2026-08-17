<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro simples de aportes de meta — só o histórico exibido em
     * "Ver aportes". Diferente de investment_operations, não move dinheiro
     * de conta nenhuma: a meta continua sendo um contador de progresso, não
     * um ativo com saldo real (mesma decisão de design do contribute()).
     */
    public function up(): void
    {
        Schema::create('goal_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financial_goal_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 19, 2);
            $table->date('contributed_at');
            $table->timestamps();
            $table->index(['financial_goal_id', 'contributed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_contributions');
    }
};
