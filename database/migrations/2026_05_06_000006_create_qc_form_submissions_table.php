<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qc_form_template_id')->constrained('qc_form_templates')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('form_number')->nullable()->index();
            $table->string('status')->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->string('year')->nullable();
            $table->string('plant')->nullable();
            $table->string('area')->nullable();
            $table->string('equipment')->nullable();
            $table->string('report_no')->nullable();
            $table->string('ovh_plant')->nullable();
            $table->string('unit')->nullable();
            $table->string('tag_num')->nullable();
            $table->date('tgl_mulai')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('durasi')->nullable();
            $table->json('general_info')->nullable();
            $table->text('note')->nullable();
            $table->json('approval_data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_form_submissions');
    }
};
