<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_form_template_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qc_form_template_id')->constrained('qc_form_templates')->cascadeOnDelete();
            $table->string('type');
            $table->string('title')->nullable();
            $table->integer('order_no')->default(0);
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_form_template_blocks');
    }
};
