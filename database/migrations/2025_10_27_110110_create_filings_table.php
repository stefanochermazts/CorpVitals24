<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('taxonomy_id')->nullable()->constrained('taxonomies')->nullOnDelete();

            $table->enum('type', ['CSV', 'XLSX', 'XBRL', 'iXBRL']);
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('hash_sha256', 64)->unique();
            $table->string('currency_origin', 3)->default('EUR');
            $table->jsonb('metadata')->nullable();

            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamp('parsed_at')->nullable();
            $table->enum('status', ['pending', 'parsing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filings');
    }
};


