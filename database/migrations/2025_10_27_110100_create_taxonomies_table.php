<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxonomies', function (Blueprint $table): void {
            $table->id();
            $table->string('name'); // es. IFRS-ESEF 2024
            $table->string('version'); // es. 2024
            $table->string('country', 2)->nullable(); // EU, IT, ...
            $table->enum('base_standard', ['IFRS', 'OIC', 'HGB', 'PCGR', 'Other']);
            $table->text('taxonomy_url')->nullable();
            $table->text('entry_point_url')->nullable();
            $table->jsonb('schema_refs')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();

            $table->unique(['name', 'version']);
            $table->index(['country', 'base_standard', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxonomies');
    }
};


