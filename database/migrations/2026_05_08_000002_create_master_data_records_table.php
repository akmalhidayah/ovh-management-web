<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_data_records', function (Blueprint $table) {
            $table->id();

            $table->string('document_category', 40);
            $table->string('year', 10)->nullable();

            $table->string('func_location', 150);
            $table->string('equipment_no', 80)->nullable();
            $table->string('section_no', 100)->nullable();
            $table->string('description', 191);

            $table->string('plant', 80);
            $table->string('area', 80);

            $table->string('status', 20)->default('active');
            $table->string('inspection_status', 20)->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['document_category', 'func_location'],
                'master_data_doc_func_location_unique'
            );

            $table->index(
                ['document_category', 'equipment_no'],
                'master_data_doc_equipment_index'
            );

            $table->index(
                ['document_category', 'plant', 'area'],
                'master_data_doc_plant_area_index'
            );

            $table->index(
                ['status', 'year'],
                'master_data_status_year_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_data_records');
    }
};
