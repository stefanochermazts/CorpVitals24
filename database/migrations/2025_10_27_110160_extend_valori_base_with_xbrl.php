<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('valori_base')) {
            return; // Table may be created later; guard to avoid failure
        }

        Schema::table('valori_base', function (Blueprint $table): void {
            if (!Schema::hasColumn('valori_base', 'filing_id')) {
                $table->foreignId('filing_id')->nullable()->after('period_id')->constrained('filings')->nullOnDelete();
            }
            if (Schema::hasColumn('valori_base', 'source')) {
                // Ensure enum extension handled at application-level; keep existing
            } else {
                $table->string('source', 16)->default('manual')->after('importo');
            }
            if (!Schema::hasColumn('valori_base', 'provenance')) {
                $table->jsonb('provenance')->nullable()->after('source');
            }

            $table->index(['filing_id', 'voce']);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('valori_base')) {
            return;
        }

        Schema::table('valori_base', function (Blueprint $table): void {
            if (Schema::hasColumn('valori_base', 'filing_id')) {
                $table->dropConstrainedForeignId('filing_id');
            }
            if (Schema::hasColumn('valori_base', 'provenance')) {
                $table->dropColumn('provenance');
            }
        });
    }
};


