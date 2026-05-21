<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_sections', function (Blueprint $table) {
            $table->id();
            $table->string('department');
            $table->string('unit_kerja');
            $table->string('section');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(
                ['department', 'unit_kerja', 'section'],
                'organization_sections_unique'
            );

            $table->index(['status', 'department'], 'organization_sections_status_department_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_sections');
    }
};
