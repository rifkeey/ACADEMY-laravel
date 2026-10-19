<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Programmes and their versioned curriculum.
 *
 * Learner history (progress, attempts, certificates) points at version-specific rows, so
 * publishing a new course version never rewrites what a learner actually studied.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specializations', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('code', 8)->unique();               // used in certificate numbers, e.g. SOC
            $table->string('title', 120);
            $table->string('category', 20);
            $table->string('level', 60);
            $table->string('summary', 400);
            $table->text('description');
            $table->json('audience');
            $table->json('prerequisites');
            $table->json('outcomes');
            $table->json('inclusions');
            $table->json('faqs');
            $table->unsignedSmallInteger('estimated_hours');
            $table->string('status', 20)->default('draft');
            $table->boolean('enrolment_open')->default(false);
            $table->string('content_status', 20)->default('in_development');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedBigInteger('current_version_id')->nullable();  // FK added below (circular)
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });

        Schema::create('course_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialization_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('version');
            $table->string('status', 20)->default('draft');
            $table->text('changelog')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['specialization_id', 'version']);
            $table->index(['specialization_id', 'status']);
        });

        Schema::table('specializations', function (Blueprint $table) {
            $table->foreign('current_version_id')->references('id')->on('course_versions')->nullOnDelete();
        });

        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_version_id')->constrained()->restrictOnDelete();
            $table->string('slug', 100);
            $table->unsignedSmallInteger('position');
            $table->string('title', 160);
            $table->text('purpose')->nullable();
            $table->text('prerequisite_knowledge')->nullable();
            $table->json('objectives');
            $table->json('key_concepts');
            $table->json('assessment_criteria');
            $table->text('instructor_notes')->nullable();
            $table->date('review_date')->nullable();
            $table->string('content_status', 20)->default('starter');
            $table->string('status', 20)->default('published');
            $table->timestamps();

            $table->unique(['course_version_id', 'slug']);
            $table->index(['course_version_id', 'status', 'position']);
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_module_id')->constrained()->restrictOnDelete();
            $table->string('slug', 120);
            $table->unsignedSmallInteger('position');
            $table->string('title', 160);
            $table->json('objectives');
            $table->longText('body_md');
            $table->unsignedSmallInteger('estimated_minutes')->default(20);
            $table->json('knowledge_checks');                 // formative only: answers may be shown in the browser
            $table->boolean('required')->default(true);
            $table->string('status', 20)->default('published');
            $table->timestamps();

            $table->unique(['course_module_id', 'slug']);
            $table->index(['course_module_id', 'status', 'position']);
        });

        Schema::create('source_references', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('title', 300);
            $table->string('publisher', 160);
            $table->string('url', 500);
            $table->string('topic', 200);
            $table->string('source_type', 30);
            $table->text('version_note')->nullable();
            $table->text('supports')->nullable();
            $table->date('date_checked')->nullable();
            $table->string('verification_status', 20)->default('pending_review');
            $table->timestamps();
        });

        Schema::create('lesson_source', function (Blueprint $table) {
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_reference_id')->constrained()->restrictOnDelete();
            $table->primary(['lesson_id', 'source_reference_id']);
        });

        // MySQL 8.0.16+ enforces CHECK constraints.
        foreach ([
            "ALTER TABLE specializations ADD CONSTRAINT spec_status_check CHECK (status IN ('draft','published','archived'))",
            "ALTER TABLE specializations ADD CONSTRAINT spec_content_check CHECK (content_status IN ('complete','in_development'))",
            "ALTER TABLE course_versions ADD CONSTRAINT version_status_check CHECK (status IN ('draft','published','retired'))",
            "ALTER TABLE course_modules ADD CONSTRAINT module_content_check CHECK (content_status IN ('complete','starter','outline'))",
            "ALTER TABLE course_modules ADD CONSTRAINT module_status_check CHECK (status IN ('draft','published','archived'))",
            "ALTER TABLE lessons ADD CONSTRAINT lesson_status_check CHECK (status IN ('draft','published','archived'))",
            "ALTER TABLE source_references ADD CONSTRAINT source_type_check CHECK (source_type IN ('official_standard','vendor_documentation','open_resource','practice_environment'))",
            "ALTER TABLE source_references ADD CONSTRAINT source_verify_check CHECK (verification_status IN ('verified','pending_review','unavailable'))",
        ] as $sql) {
            DB::statement($sql);
        }
    }

    public function down(): void
    {
        Schema::table('specializations', fn (Blueprint $t) => $t->dropForeign(['current_version_id']));
        Schema::dropIfExists('lesson_source');
        Schema::dropIfExists('source_references');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('course_modules');
        Schema::dropIfExists('course_versions');
        Schema::dropIfExists('specializations');
    }
};
