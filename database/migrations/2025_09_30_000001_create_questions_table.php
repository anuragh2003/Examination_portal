<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('text'); // full question text
            $table->enum('type', ['mcq_single', 'mcq_multiple', 'descriptive']);
            $table->integer('marks')->unsigned();
            $table->string('difficulty')->nullable(); // easy, medium, hard (optional)
            $table->string('tags')->nullable();       // comma-separated tags (optional)
            $table->string('status')->default('active'); // active/inactive — used by selector
            $table->string('import_hash', 64)->nullable()->unique(); // prevent duplicate CSV rows
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};