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
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('shuffled_question_ids');
        });
        
        Schema::table('exam_instances', function (Blueprint $table) {
            $table->dropColumn('shuffled_question_ids');
        });
        
        Schema::table('student_answers', function (Blueprint $table) {
            $table->dropColumn('chosen_option_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->json('shuffled_question_ids')->nullable()->after('otp_expires_at');
        });
        
        Schema::table('exam_instances', function (Blueprint $table) {
            $table->json('shuffled_question_ids')->nullable()->change();
        });
        
        Schema::table('student_answers', function (Blueprint $table) {
            $table->longText('chosen_option_ids')->nullable()->after('question_id');
        });
    }
};
