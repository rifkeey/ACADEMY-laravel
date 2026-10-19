<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Practical labs and examinations.
 *
 * Answer keys (lab validators, correct options, attempt snapshots) are stored server-side and
 * are never serialised to the browser.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_version_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_module_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug', 120);
            $table->unsignedSmallInteger('position');
            $table->string('title', 160);
            $table->string('difficulty', 20);
            $table->string('lab_type', 40);
            $table->string('summary', 600);
            $table->unsignedSmallInteger('estimated_minutes')->default(45);
            $table->boolean('required')->default(true);
            $table->unsignedTinyInteger('pass_percent')->default(80);
            $table->json('content');            // learner-visible: scenario, workspace, tasks, hints
            $table->json('validator');          // server-only answer keys
            $table->string('status', 20)->default('published');
            $table->timestamps();

            $table->unique(['course_version_id', 'slug']);
            $table->index(['course_version_id', 'status', 'position']);
        });

        Schema::create('lab_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('lab_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrolment_id')->constrained()->restrictOnDelete();
            $table->json('answers');
            $table->json('task_results');
            $table->unsignedSmallInteger('score');
            $table->unsignedSmallInteger('max_score');
            $table->boolean('passed');
            $table->unsignedSmallInteger('hints_used')->default(0);
            $table->timestamp('submitted_at')->useCurrent();

            $table->index(['user_id', 'lab_id', 'submitted_at']);
        });

        Schema::create('lab_completions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('lab_id')->constrained()->restrictOnDelete();
            $table->foreignId('lab_attempt_id')->constrained()->restrictOnDelete();
            $table->timestamp('completed_at')->useCurrent();

            $table->primary(['user_id', 'lab_id']);       // first pass is the completion record
        });

        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialization_id')->unique()->constrained()->restrictOnDelete();
            $table->string('title', 160);
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->restrictOnDelete();
            $table->string('external_key', 60);
            $table->string('kind', 10);                      // single | multiple
            $table->string('category', 60);
            $table->string('difficulty', 20);
            $table->string('objective', 300)->nullable();
            $table->json('tags');
            $table->text('prompt');
            $table->text('explanation')->nullable();
            $table->string('review_status', 20)->default('draft');
            $table->timestamps();

            $table->unique(['question_bank_id', 'external_key']);
            $table->index(['question_bank_id', 'review_status', 'category']);
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->text('text');
            $table->boolean('is_correct')->default(false);

            $table->index(['question_id', 'position']);
        });

        // Examination settings per course version.
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_version_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('question_bank_id')->constrained()->restrictOnDelete();
            $table->string('title', 160);
            $table->unsignedSmallInteger('question_count')->default(40);
            $table->unsignedTinyInteger('pass_percent')->default(75);
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->unsignedTinyInteger('max_attempts')->default(3);
            $table->unsignedSmallInteger('cooldown_hours')->default(24);
            $table->boolean('show_explanations')->default(true);
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('config_version')->default(1);
            $table->timestamps();
        });

        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();            // used in URLs instead of the numeric id
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('exam_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrolment_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('attempt_number');
            $table->unsignedInteger('exam_config_version');
            $table->unsignedTinyInteger('pass_percent');     // snapshot: later config changes do not alter this attempt
            $table->string('status', 20)->default('in_progress');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('expires_at');
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedSmallInteger('correct_count')->nullable();
            $table->unsignedSmallInteger('total_count');
            $table->boolean('passed')->nullable();
            // MySQL has no partial indexes: this generated column is 1 only while in progress and NULL
            // otherwise, so the unique index below allows exactly one open attempt per learner per exam.
            $table->unsignedTinyInteger('open_marker')->nullable()->storedAs("IF(`status` = 'in_progress', 1, NULL)");
            $table->timestamps();

            $table->unique(['user_id', 'exam_id', 'attempt_number']);
            $table->unique(['user_id', 'exam_id', 'open_marker']);
            $table->index(['enrolment_id', 'passed']);
        });

        Schema::create('exam_attempt_questions', function (Blueprint $table) {
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->json('snapshot');                        // prompt, shuffled options with correctness, explanation
            $table->json('selected_option_ids')->nullable();
            $table->boolean('is_correct')->nullable();

            $table->primary(['exam_attempt_id', 'position']);
        });

        foreach ([
            "ALTER TABLE labs ADD CONSTRAINT labs_status_check CHECK (status IN ('draft','published','archived'))",
            'ALTER TABLE labs ADD CONSTRAINT labs_pass_check CHECK (pass_percent BETWEEN 1 AND 100)',
            "ALTER TABLE questions ADD CONSTRAINT questions_kind_check CHECK (kind IN ('single','multiple'))",
            "ALTER TABLE questions ADD CONSTRAINT questions_difficulty_check CHECK (difficulty IN ('foundation','intermediate','advanced'))",
            "ALTER TABLE questions ADD CONSTRAINT questions_review_check CHECK (review_status IN ('draft','approved','retired'))",
            "ALTER TABLE exams ADD CONSTRAINT exams_status_check CHECK (status IN ('draft','published'))",
            'ALTER TABLE exams ADD CONSTRAINT exams_pass_check CHECK (pass_percent BETWEEN 1 AND 100)',
            'ALTER TABLE exams ADD CONSTRAINT exams_count_check CHECK (question_count BETWEEN 1 AND 200)',
            'ALTER TABLE exams ADD CONSTRAINT exams_duration_check CHECK (duration_minutes BETWEEN 5 AND 480)',
            "ALTER TABLE exam_attempts ADD CONSTRAINT attempts_status_check CHECK (status IN ('in_progress','submitted'))",
        ] as $sql) {
            DB::statement($sql);
        }
    }

    public function down(): void
    {
        foreach (['exam_attempt_questions', 'exam_attempts', 'exams', 'question_options', 'questions', 'question_banks', 'lab_completions', 'lab_attempts', 'labs'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
