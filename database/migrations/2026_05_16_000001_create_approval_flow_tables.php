<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_approval_steps', function (Blueprint $table) {
            $table->id();
            $table->string('template_type');
            $table->unsignedBigInteger('template_id');
            $table->unsignedInteger('step_order');
            $table->string('label');
            $table->boolean('is_submitter_signature')->default(false);
            $table->boolean('requires_magic_link')->default(true);
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->index(['template_type', 'template_id', 'step_order'], 'tmpl_appr_steps_lookup_idx');
        });

        Schema::create('approval_flows', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable');
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('current_step_order')->nullable();
            $table->timestamps();
        });

        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_flow_id')->constrained('approval_flows')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('label');
            $table->boolean('is_submitter_signature')->default(false);
            $table->boolean('requires_magic_link')->default(true);
            $table->string('status')->default('pending')->index();
            $table->string('approver_name')->nullable();
            $table->string('approver_position')->nullable();
            $table->string('approver_email')->nullable();
            $table->string('approver_phone')->nullable();
            $table->string('signature_path')->nullable();
            $table->longText('signature_data')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->unique(['approval_flow_id', 'step_order']);
        });

        Schema::create('approval_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_step_id')->constrained('approval_steps')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('approval_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_flow_id')->nullable()->constrained('approval_flows')->nullOnDelete();
            $table->foreignId('approval_step_id')->nullable()->constrained('approval_steps')->nullOnDelete();
            $table->string('event');
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_events');
        Schema::dropIfExists('approval_links');
        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('approval_flows');
        Schema::dropIfExists('template_approval_steps');
    }
};
