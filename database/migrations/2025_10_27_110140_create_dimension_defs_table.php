<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dimension_defs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('taxonomy_id')->constrained('taxonomies')->cascadeOnDelete();
            $table->string('dimension_qname');
            $table->enum('dimension_type', ['explicit', 'typed']);
            $table->string('domain_qname')->nullable();
            $table->jsonb('member_list')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['taxonomy_id', 'dimension_qname']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dimension_defs');
    }
};


