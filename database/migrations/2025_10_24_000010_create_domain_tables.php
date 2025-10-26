<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->json('settings_json')->nullable();
            $t->timestamps();
        });

        Schema::create('companies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('sector')->nullable();
            $t->char('base_currency', 3)->default('EUR');
            $t->unsignedTinyInteger('fiscal_year_start')->default(1);
            $t->timestamps();
            $t->index(['tenant_id','name']);
        });

        Schema::create('periods', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->string('kind', 16)->default('M');
            $t->date('start');
            $t->date('end');
            $t->char('currency', 3)->default('EUR');
            $t->timestamps();
            $t->index(['company_id','start']);
        });

        Schema::create('kpis', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->text('description')->nullable();
            $t->jsonb('formula_refs')->nullable();
            $t->timestamps();
        });

        Schema::create('kpi_values', function (Blueprint $t) {
            $t->id();
            $t->foreignId('period_id')->constrained()->cascadeOnDelete();
            $t->foreignId('kpi_id')->constrained()->cascadeOnDelete();
            $t->decimal('value', 18, 6)->nullable();
            $t->string('unit', 8)->default('%');
            $t->string('state', 12)->nullable();
            $t->jsonb('provenance_json')->nullable();
            $t->timestamps();
            $t->unique(['period_id','kpi_id']);
            $t->index(['kpi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_values');
        Schema::dropIfExists('kpis');
        Schema::dropIfExists('periods');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('tenants');
    }
};


