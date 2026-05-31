<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_data_records', function (Blueprint $table): void {
            if (! Schema::hasColumn('master_data_records', 'organization_section_id')) {
                $table->foreignId('organization_section_id')
                    ->nullable()
                    ->after('area')
                    ->constrained('organization_sections')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('master_data_records', function (Blueprint $table): void {
            if (Schema::hasColumn('master_data_records', 'organization_section_id')) {
                $table->dropConstrainedForeignId('organization_section_id');
            }
        });
    }
};
