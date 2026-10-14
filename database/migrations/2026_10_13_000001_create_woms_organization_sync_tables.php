<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('woms_personnel', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('woms_user_id')->unique();
            $table->string('name', 255);
            $table->string('email', 255)->nullable();
            $table->boolean('present_in_source')->default(true)->index();
            $table->timestamp('source_synced_at');
            $table->timestamp('source_missing_at')->nullable();
            $table->timestamps();
        });

        Schema::create('woms_departments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('woms_id')->unique();
            $table->string('name', 255);
            $table->unsignedBigInteger('general_manager_woms_user_id')->nullable();
            $table->boolean('present_in_source')->default(true)->index();
            $table->timestamp('source_synced_at');
            $table->timestamp('source_missing_at')->nullable();
            $table->timestamps();

            $table->foreign('general_manager_woms_user_id', 'woms_departments_gm_user_fk')
                ->references('woms_user_id')
                ->on('woms_personnel')
                ->nullOnDelete();
        });

        Schema::create('woms_units', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('woms_id')->unique();
            $table->unsignedBigInteger('woms_department_id');
            $table->string('name', 255);
            $table->unsignedBigInteger('senior_manager_woms_user_id')->nullable();
            $table->boolean('present_in_source')->default(true)->index();
            $table->timestamp('source_synced_at');
            $table->timestamp('source_missing_at')->nullable();
            $table->timestamps();

            $table->foreign('woms_department_id', 'woms_units_department_fk')
                ->references('woms_id')
                ->on('woms_departments')
                ->restrictOnDelete();
            $table->foreign('senior_manager_woms_user_id', 'woms_units_sm_user_fk')
                ->references('woms_user_id')
                ->on('woms_personnel')
                ->nullOnDelete();
            $table->index(['woms_department_id', 'present_in_source'], 'woms_units_department_presence_index');
        });

        Schema::create('woms_sections', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('woms_id')->unique();
            $table->unsignedBigInteger('woms_unit_id');
            $table->string('name', 255);
            $table->unsignedBigInteger('manager_woms_user_id')->nullable();
            $table->boolean('present_in_source')->default(true)->index();
            $table->timestamp('source_synced_at');
            $table->timestamp('source_missing_at')->nullable();
            $table->timestamps();

            $table->foreign('woms_unit_id', 'woms_sections_unit_fk')
                ->references('woms_id')
                ->on('woms_units')
                ->restrictOnDelete();
            $table->foreign('manager_woms_user_id', 'woms_sections_manager_user_fk')
                ->references('woms_user_id')
                ->on('woms_personnel')
                ->nullOnDelete();
            $table->index(['woms_unit_id', 'present_in_source'], 'woms_sections_unit_presence_index');
        });

        Schema::create('woms_organization_sync_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('status', 32)->index();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('source_generated_at')->nullable();
            $table->string('source_checksum', 64)->nullable()->index();
            $table->unsignedInteger('schema_version')->nullable();
            $table->unsignedInteger('department_count')->default(0);
            $table->unsignedInteger('unit_count')->default(0);
            $table->unsignedInteger('section_count')->default(0);
            $table->unsignedInteger('personnel_count')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->unsignedInteger('marked_missing_count')->default(0);
            $table->string('error_category', 64)->nullable();
            $table->string('sanitized_error_message', 500)->nullable();
            $table->string('triggered_by_type', 32);
            // Logical reference to the legacy MyISAM users table.
            $table->unsignedBigInteger('triggered_by_user_id')->nullable()->index();
            $table->timestamps();

            $table->index(['status', 'finished_at'], 'woms_sync_runs_status_finished_index');
        });

        Schema::create('organization_section_mappings', function (Blueprint $table): void {
            $table->id();
            // Logical reference to the legacy MyISAM organization_sections table.
            $table->unsignedBigInteger('organization_section_id');
            $table->foreignId('woms_section_id')
                ->constrained('woms_sections')
                ->cascadeOnDelete();
            $table->string('mapping_status', 24)->default('pending')->index();
            // Logical reference to the legacy MyISAM users table.
            $table->unsignedBigInteger('verified_by')->nullable()->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique('organization_section_id', 'organization_section_mappings_legacy_unique');
            $table->unique('woms_section_id', 'organization_section_mappings_woms_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_section_mappings');
        Schema::dropIfExists('woms_organization_sync_runs');
        Schema::dropIfExists('woms_sections');
        Schema::dropIfExists('woms_units');
        Schema::dropIfExists('woms_departments');
        Schema::dropIfExists('woms_personnel');
    }
};
