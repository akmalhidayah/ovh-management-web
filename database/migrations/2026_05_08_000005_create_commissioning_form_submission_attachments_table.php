<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissioning_form_submission_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commissioning_form_submission_id');
            $table->string('field_key');
            $table->string('label')->nullable();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('type', 20)->default('file');
            $table->timestamps();

            $table->foreign('commissioning_form_submission_id', 'comm_form_attach_submission_fk')
                ->references('id')
                ->on('commissioning_form_submissions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissioning_form_submission_attachments');
    }
};
