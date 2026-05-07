<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_form_template_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qc_form_template_id')->constrained('qc_form_templates')->cascadeOnDelete();
            $table->foreignId('qc_form_template_block_id')->nullable()->constrained('qc_form_template_blocks')->cascadeOnDelete();
            $table->string('field_name');
            $table->string('label');
            $table->string('type');
            $table->boolean('required')->default(false);
            $table->boolean('readonly')->default(false);
            $table->json('options')->nullable();
            $table->string('unit')->nullable();
            $table->text('help_text')->nullable();
            $table->json('validation_rules')->nullable();
            $table->integer('order_no')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_form_template_fields');
    }
};
