<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_form_submission_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qc_form_submission_id')->constrained('qc_form_submissions')->cascadeOnDelete();
            $table->foreignId('qc_form_template_block_id')->nullable()->constrained('qc_form_template_blocks')->nullOnDelete();
            $table->string('block_type')->nullable();
            $table->integer('order_no')->default(0);
            $table->json('row_data')->nullable();
            $table->string('status_value')->nullable();
            $table->text('catatan')->nullable();
            $table->text('aktual')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_form_submission_rows');
    }
};
