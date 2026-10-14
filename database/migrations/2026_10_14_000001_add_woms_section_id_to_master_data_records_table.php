<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_data_records', function (Blueprint $table): void {
            $table->unsignedBigInteger('woms_section_id')
                ->nullable()
                ->after('organization_section_id');
            $table->index('woms_section_id', 'master_data_records_woms_section_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('master_data_records', function (Blueprint $table): void {
            $table->dropIndex('master_data_records_woms_section_id_index');
            $table->dropColumn('woms_section_id');
        });
    }
};
