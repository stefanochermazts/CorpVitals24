<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filing_facts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('filing_id')->constrained('filings')->cascadeOnDelete();

            // Concept
            $table->string('concept_qname');
            $table->string('concept_label')->nullable();

            // Context
            $table->string('context_ref');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->enum('period_type', ['instant', 'duration'])->default('duration');

            // Unit
            $table->string('unit_ref')->nullable();
            $table->integer('decimals')->nullable();

            // Values
            $table->decimal('value_raw', 20, 6);
            $table->decimal('value_normalized', 20, 6)->nullable();

            // Dimensions JSONB
            $table->jsonb('dimensions')->nullable();

            $table->text('notes')->nullable();
            $table->timestamp('extracted_at')->useCurrent();
            $table->timestamps();

            $table->index(['filing_id', 'concept_qname']);
            $table->index(['filing_id', 'period_start', 'period_end']);
            $table->index('context_ref');
        });

        // Add GIN index for JSONB dimensions (PostgreSQL)
        DB::statement("CREATE INDEX filing_facts_dimensions_gin ON filing_facts USING GIN (dimensions);");
    }

    public function down(): void
    {
        Schema::dropIfExists('filing_facts');
    }
};


