<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();

            $table->string('source_column');
            $table->string('target_field'); // es: Ricavi, COGS, etc.
            $table->jsonb('transformation_rule')->nullable(); // es: trim, replace, multiplier

            $table->timestamps();

            $table->index(['import_id', 'target_field']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_mappings');
    }
};


