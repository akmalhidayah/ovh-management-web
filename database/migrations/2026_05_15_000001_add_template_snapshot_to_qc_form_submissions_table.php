<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qc_form_submissions', function (Blueprint $table) {
            $table->string('template_code')->nullable()->after('qc_form_template_id');
            $table->string('template_name')->nullable()->after('template_code');
            $table->unsignedInteger('template_version')->nullable()->after('template_name');
            $table->json('template_snapshot')->nullable()->after('template_version');
        });
    }

    public function down(): void
    {
        Schema::table('qc_form_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'template_code',
                'template_name',
                'template_version',
                'template_snapshot',
            ]);
        });
    }
};
