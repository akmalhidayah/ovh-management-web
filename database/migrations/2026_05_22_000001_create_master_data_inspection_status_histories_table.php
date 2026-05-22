<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_data_inspection_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_data_record_id')->constrained()->cascadeOnDelete();
            $table->string('previous_status', 20)->nullable();
            $table->string('status', 20);
            $table->string('source', 40)->default('manual_admin');
            $table->nullableMorphs('submission');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->useCurrent();
            $table->json('snapshot');
            $table->timestamps();

            $table->index(['master_data_record_id', 'status'], 'mdi_hist_record_status_index');
            $table->index(['source', 'changed_at'], 'mdi_hist_source_changed_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_data_inspection_status_histories');
    }
};
