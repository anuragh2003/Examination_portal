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
        Schema::create('proctor_screenshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('exam_id');
            $table->enum('frame_type', ['screen', 'face']); // type of screenshot
            $table->unsignedInteger('frame_number'); // frame sequence number
            $table->dateTime('timestamp'); // when screenshot was taken
            $table->string('filename'); // stored filename with path
            $table->string('original_name'); // original file name
            $table->string('mime_type'); // image/jpeg, image/png
            $table->bigInteger('size'); // bytes
            $table->timestamps();

            // Create indices for faster queries
            $table->index(['student_id', 'exam_id']);
            $table->index(['exam_id', 'frame_type']);
            $table->index('frame_number');

            // Foreign keys
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proctor_screenshots');
    }
};
