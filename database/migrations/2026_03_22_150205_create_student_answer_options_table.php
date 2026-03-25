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
        Schema::create('student_answer_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_answer_id')->constrained('student_answers')->onDelete('cascade');
            $table->foreignId('question_option_id')->constrained('question_options')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['student_answer_id', 'question_option_id'], 'student_answer_option_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_answer_options');
    }
};
