<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qc_form_templates', function (Blueprint $table) {
            $table->string('template_type')->nullable()->after('layout_mode')->index();
            $table->json('body_schema')->nullable()->after('template_type');
        });

        Schema::table('qc_form_submissions', function (Blueprint $table) {
            $table->json('body_data')->nullable()->after('general_info');
        });
    }

    public function down(): void
    {
        Schema::table('qc_form_submissions', function (Blueprint $table) {
            $table->dropColumn('body_data');
        });

        Schema::table('qc_form_templates', function (Blueprint $table) {
            $table->dropColumn(['template_type', 'body_schema']);
        });
    }
};
