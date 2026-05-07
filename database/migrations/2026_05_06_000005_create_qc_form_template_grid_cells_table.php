<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_form_template_grid_cells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qc_form_template_id')->constrained('qc_form_templates')->cascadeOnDelete();
            $table->foreignId('qc_form_template_block_id')->nullable()->constrained('qc_form_template_blocks')->cascadeOnDelete();
            $table->integer('row_start');
            $table->integer('col_start');
            $table->integer('row_span')->default(1);
            $table->integer('col_span')->default(1);
            $table->string('cell_type');
            $table->string('field_name')->nullable();
            $table->string('label')->nullable();
            $table->text('value_default')->nullable();
            $table->string('input_type')->nullable();
            $table->json('options')->nullable();
            $table->boolean('readonly')->default(false);
            $table->string('css_class')->nullable();
            $table->json('style')->nullable();
            $table->integer('order_no')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_form_template_grid_cells');
    }
};
