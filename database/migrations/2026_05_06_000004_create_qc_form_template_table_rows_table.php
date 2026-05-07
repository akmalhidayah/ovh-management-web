<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_form_template_table_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qc_form_template_id')->constrained('qc_form_templates')->cascadeOnDelete();
            $table->foreignId('qc_form_template_block_id')->constrained('qc_form_template_blocks')->cascadeOnDelete();
            $table->integer('order_no')->default(0);
            $table->json('row_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_form_template_table_rows');
    }
};
