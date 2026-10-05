<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->unsignedBigInteger('admin_id')->nullable()->change();
            $table->foreign('admin_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->after('user_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('notification_type')->default('admin')->index()->after('task_id');
            $table->string('deduplication_key')->nullable()->unique()->after('notification_type');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropUnique(['deduplication_key']);
            $table->dropColumn(['task_id', 'notification_type', 'deduplication_key']);
            $table->dropForeign(['admin_id']);
            $table->unsignedBigInteger('admin_id')->nullable(false)->change();
            $table->foreign('admin_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
