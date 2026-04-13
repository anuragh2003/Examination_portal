<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'role')) {
                $table->string('role')->after('duration_minutes')->default('general');
            }
        });

        if (Schema::hasColumn('students', 'exam_id')) {
            DB::statement('ALTER TABLE students MODIFY exam_id BIGINT UNSIGNED NULL');
        }

        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'role')) {
                $table->string('role')->after('candidate_city')->nullable();
            }

            if (!Schema::hasColumn('students', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('registered_at');
            }

            if (!Schema::hasColumn('students', 'active_session')) {
                $table->boolean('active_session')->default(false)->after('started_at');
            }

            if (!Schema::hasColumn('students', 'active_session_started_at')) {
                $table->timestamp('active_session_started_at')->nullable()->after('active_session');
            }

            if (!Schema::hasColumn('students', 'active_session_expires_at')) {
                $table->timestamp('active_session_expires_at')->nullable()->after('active_session_started_at');
            }

            if (!Schema::hasColumn('students', 'session_token')) {
                $table->string('session_token')->nullable()->after('active_session_expires_at');
            }

            if (!Schema::hasColumn('students', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('session_token');
            }

            if (!Schema::hasColumn('students', 'attempt_completed')) {
                $table->boolean('attempt_completed')->default(false)->after('submitted_at');
            }

            $table->index(['candidate_email', 'role'], 'students_email_role_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'attempt_completed')) {
                $table->dropColumn('attempt_completed');
            }
            if (Schema::hasColumn('students', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }
            if (Schema::hasColumn('students', 'session_token')) {
                $table->dropColumn('session_token');
            }
            if (Schema::hasColumn('students', 'active_session_expires_at')) {
                $table->dropColumn('active_session_expires_at');
            }
            if (Schema::hasColumn('students', 'active_session_started_at')) {
                $table->dropColumn('active_session_started_at');
            }
            if (Schema::hasColumn('students', 'active_session')) {
                $table->dropColumn('active_session');
            }
            if (Schema::hasColumn('students', 'started_at')) {
                $table->dropColumn('started_at');
            }
            if (Schema::hasColumn('students', 'role')) {
                $table->dropColumn('role');
            }
            $table->dropIndex('students_email_role_index');
            if (Schema::hasColumn('students', 'exam_id')) {
                DB::statement('ALTER TABLE students MODIFY exam_id BIGINT UNSIGNED NOT NULL');
            }
        });

        Schema::table('exams', function (Blueprint $table) {
            if (Schema::hasColumn('exams', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
