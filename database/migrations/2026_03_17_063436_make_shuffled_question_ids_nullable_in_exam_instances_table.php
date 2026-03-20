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
        Schema::table('exam_instances', function (Blueprint $table) {
            $table->json('shuffled_question_ids')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('exam_instances', function (Blueprint $table) {
            $table->json('shuffled_question_ids')->nullable(false)->change();
        });
    }
};
