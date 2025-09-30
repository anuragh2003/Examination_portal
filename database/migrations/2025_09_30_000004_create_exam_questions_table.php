<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')
                  ->constrained('exams')
                  ->onDelete('cascade');
            $table->foreignId('question_id')
                  ->constrained('questions')
                  ->onDelete('cascade');
            $table->integer('order_position')->default(0); // question order in exam
            $table->timestamps();
            
            // Ensure same question can't be added to same exam twice
            $table->unique(['exam_id', 'question_id']);
            
            // Index for faster queries
            $table->index(['exam_id', 'order_position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};