<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('qc')->after('usertype');
            });
        }

        DB::table('users')
            ->where('usertype', 'inspector')
            ->update([
                'usertype' => 'user',
                'role' => 'qc',
            ]);

        DB::table('users')
            ->where('usertype', 'admin')
            ->whereNull('role')
            ->update(['role' => 'admin']);

        DB::table('users')
            ->where('usertype', 'user')
            ->whereNull('role')
            ->update(['role' => 'qc']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('usertype')->default('user')->change();
            $table->string('role')->default('qc')->change();
        });
    }

    public function down(): void
    {
        DB::table('users')
            ->where('usertype', 'user')
            ->where('role', 'qc')
            ->update(['usertype' => 'inspector']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('usertype')->default('inspector')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
