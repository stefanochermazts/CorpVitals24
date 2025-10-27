<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxonomy_maps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('taxonomy_id')->constrained('taxonomies')->cascadeOnDelete();

            $table->string('concept_qname');
            $table->string('valore_base_target');
            $table->enum('sign_rule', ['positive', 'negative', 'absolute', 'preserve'])->default('preserve');
            $table->decimal('multiplier', 10, 6)->default(1.0);
            $table->integer('priority')->default(100);
            $table->jsonb('transformation_rules')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->index(['taxonomy_id', 'concept_qname']);
            $table->index('valore_base_target');
            $table->unique(['taxonomy_id', 'concept_qname', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxonomy_maps');
    }
};


