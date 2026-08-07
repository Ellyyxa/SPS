<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('moods', function (Blueprint $table) {

        $table->id();

        $table->foreignId('user_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->enum('mood', [
            'Happy',
            'Neutral',
            'Sad',
            'Stress',
            'Angry'
        ]);

        $table->text('note')
              ->nullable();

        $table->date('date');

        $table->timestamps();

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moods');
    }
};
