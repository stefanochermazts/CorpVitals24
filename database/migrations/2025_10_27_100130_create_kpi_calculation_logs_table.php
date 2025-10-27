<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_calculation_logs', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('kpi_id')->constrained('kpis')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('periods')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('source_import_id')->nullable()->constrained('imports')->nullOnDelete();

            $table->timestamp('calculated_at')->useCurrent();
            $table->text('formula_used');
            $table->decimal('result', 20, 6)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'period_id']);
            $table->index(['kpi_id', 'period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_calculation_logs');
    }
};


