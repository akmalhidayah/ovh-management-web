<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_form_submission_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qc_form_submission_id')->constrained('qc_form_submissions')->cascadeOnDelete();
            $table->string('field_key');
            $table->string('label')->nullable();
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_form_submission_attachments');
    }
};
