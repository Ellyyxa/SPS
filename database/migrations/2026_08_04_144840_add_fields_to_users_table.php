<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {

        $table->string('student_id')->unique()->nullable()->after('id');

        $table->string('course')->nullable()->after('email');

        $table->string('semester')->nullable()->after('course');

    });
}

    public function down(): void
{
    Schema::table('users', function (Blueprint $table) {

        $table->dropColumn([
            'student_id',
            'course',
            'semester'
        ]);

    });
}
};