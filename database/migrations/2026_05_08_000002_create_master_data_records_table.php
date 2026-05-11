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
            $table->string('func_location');
            $table->string('equipment_no', 80);
            $table->string('section_no')->nullable();
            $table->string('description');
            $table->string('plant');
            $table->string('area');
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['document_category', 'equipment_no'], 'master_data_doc_equipment_unique');
            $table->index(['document_category', 'plant', 'area']);
            $table->index(['status', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_data_records');
    }
};
