<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dimension_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('filing_fact_id')->constrained('filing_facts')->cascadeOnDelete();
            $table->foreignId('dimension_def_id')->nullable()->constrained('dimension_defs')->nullOnDelete();
            $table->string('dimension_qname');
            $table->string('member_qname')->nullable();
            $table->text('typed_value')->nullable();
            $table->integer('axis_order')->default(0);
            $table->timestamps();

            $table->index(['filing_fact_id', 'dimension_qname']);
            $table->index('member_qname');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dimension_values');
    }
};


