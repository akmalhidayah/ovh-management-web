<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Before running this in production, verify legacy data has no duplicate form_number:
        // SELECT form_number, COUNT(*) AS total FROM qc_form_submissions GROUP BY form_number HAVING COUNT(*) > 1;
        Schema::table('qc_form_submissions', function (Blueprint $table) {
            $table->dropIndex(['form_number']);
            $table->unique('form_number');
        });
    }

    public function down(): void
    {
        Schema::table('qc_form_submissions', function (Blueprint $table) {
            $table->dropUnique(['form_number']);
            $table->index('form_number');
        });
    }
};
