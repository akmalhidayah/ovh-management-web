<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'admin_role') && ! Schema::hasColumn('users', 'secondary_role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('secondary_role')->nullable()->after('role');
            });

            return;
        }

        if (Schema::hasColumn('users', 'admin_role') && ! Schema::hasColumn('users', 'secondary_role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('admin_role', 'secondary_role');
            });

            return;
        }

        if (Schema::hasColumn('users', 'admin_role') && Schema::hasColumn('users', 'secondary_role')) {
            DB::table('users')
                ->whereNull('secondary_role')
                ->whereNotNull('admin_role')
                ->update(['secondary_role' => DB::raw('admin_role')]);

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('admin_role');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'secondary_role') && ! Schema::hasColumn('users', 'admin_role')) {
            if ($this->legacyAdminRoleMigrationRan()) {
                Schema::table('users', function (Blueprint $table) {
                    $table->renameColumn('secondary_role', 'admin_role');
                });
            } else {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('secondary_role');
                });
            }

            return;
        }

        if (Schema::hasColumn('users', 'secondary_role') && Schema::hasColumn('users', 'admin_role')) {
            DB::table('users')
                ->whereNull('admin_role')
                ->whereNotNull('secondary_role')
                ->update(['admin_role' => DB::raw('secondary_role')]);

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('secondary_role');
            });
        }
    }

    private function legacyAdminRoleMigrationRan(): bool
    {
        return Schema::hasTable('migrations')
            && DB::table('migrations')
                ->where('migration', '2026_06_02_000001_add_admin_role_to_users_table')
                ->exists();
    }
};
