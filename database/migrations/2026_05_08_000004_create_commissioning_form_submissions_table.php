<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissioning_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commissioning_form_template_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('form_number')->unique();
            $table->string('status', 20)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->string('year')->nullable();
            $table->string('area')->nullable();
            $table->string('equipment')->nullable();
            $table->string('equipment_no')->nullable();
            $table->string('tag_num')->nullable();
            $table->string('functional_location')->nullable();
            $table->json('header_data')->nullable();
            $table->json('body_data')->nullable();
            $table->text('note')->nullable();
            $table->json('approval_data')->nullable();
            $table->timestamps();

            $table->foreign('commissioning_form_template_id', 'comm_form_sub_template_fk')
                ->references('id')
                ->on('commissioning_form_templates')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissioning_form_submissions');
    }
};
