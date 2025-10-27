<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raw_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();

            $table->unsignedBigInteger('row_number');
            $table->jsonb('data');
            $table->boolean('validated')->default(false);
            $table->jsonb('errors')->nullable(); // array di messaggi o codici

            $table->timestamps();

            $table->index(['import_id', 'row_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_rows');
    }
};


