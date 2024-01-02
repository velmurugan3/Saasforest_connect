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
        Schema::create('learning_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');  // course_id = foreign
            $table->foreignId('user_id')->constrained()->onDelete('cascade');  // course_id = foreign
            $table->string('progress')->nullable(); //videos watched duration
            $table->string('quiz')->nullable();
            $table->longText('feedback')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_employees');
    }
};
