<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moods', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'date'],
                'moods_user_id_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('moods', function (Blueprint $table) {
            $table->dropUnique('moods_user_id_date_unique');
        });
    }
};