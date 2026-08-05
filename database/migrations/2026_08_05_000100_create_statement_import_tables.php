<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->string('filename');
            $table->string('format', 8);
            $table->string('status', 16)->default('pending');
            $table->string('stored_path')->nullable();
            $table->unsignedInteger('rows_read')->default(0);
            $table->unsignedInteger('rows_imported')->default(0);
            $table->unsignedInteger('rows_duplicated')->default(0);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('import_batch_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained()->cascadeOnDelete();
            $table->date('posted_at');
            $table->text('description_raw');
            $table->string('description');
            $table->string('type', 16);
            $table->decimal('amount', 19, 2);
            $table->string('external_id')->nullable();
            $table->string('fingerprint', 64);
            $table->foreignId('suggested_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('status', 24)->default('new');
            $table->boolean('included')->default(true);
            $table->unsignedInteger('line_number')->nullable();
            $table->timestamps();
            $table->index(['import_batch_id', 'status']);
        });

        Schema::create('import_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('institution_hint');
            $table->json('column_map');
            $table->string('date_format', 16);
            $table->string('decimal_separator', 8);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'institution_hint']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('external_id')->nullable()->after('source_id');
            $table->foreignId('import_batch_id')->nullable()->after('external_id')
                ->constrained()->nullOnDelete();
            $table->unique(['user_id', 'account_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'account_id', 'external_id']);
            $table->dropConstrainedForeignId('import_batch_id');
            $table->dropColumn('external_id');
        });

        Schema::dropIfExists('import_mappings');
        Schema::dropIfExists('import_batch_rows');
        Schema::dropIfExists('import_batches');
    }
};
