<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_data_records', function (Blueprint $table) {
            if (! Schema::hasColumn('master_data_records', 'inspection_status')) {
                $table->string('inspection_status', 20)->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('master_data_records', function (Blueprint $table) {
            if (Schema::hasColumn('master_data_records', 'inspection_status')) {
                $table->dropColumn('inspection_status');
            }
        });
    }
};
