<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_submission_notification_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('submission_type');
            $table->unsignedBigInteger('submission_id');
            $table->string('status', 40);
            $table->string('notification_key');
            $table->timestamp('read_at');
            $table->timestamps();

            $table->unique(['user_id', 'notification_key'], 'admin_sub_notif_reads_unique');
            $table->index(['user_id'], 'admin_sub_notif_reads_user_idx');
            $table->index(['submission_type', 'submission_id'], 'admin_sub_notif_reads_submission_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_submission_notification_reads');
    }
};
