<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Badges, certificates and their audit trail; enquiries, articles, settings and the admin audit log. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badge_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialization_id')->unique()->constrained()->restrictOnDelete();
            $table->string('name', 120);
            $table->string('description', 600);
            $table->string('criteria', 600);
            $table->unsignedSmallInteger('hue');             // badge colour, 0–359
            $table->string('icon', 20);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('badge_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('badge_definition_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrolment_id')->unique()->constrained()->restrictOnDelete();   // one badge per enrolment
            $table->foreignId('course_version_id')->constrained()->restrictOnDelete();
            $table->char('credential_id', 26)->unique();
            $table->timestamp('awarded_at')->useCurrent();
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrolment_id')->unique()->constrained()->restrictOnDelete();   // one certificate per enrolment
            $table->foreignId('specialization_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_version_id')->constrained()->restrictOnDelete();
            $table->foreignId('exam_attempt_id')->constrained()->restrictOnDelete();
            $table->string('certificate_number', 40)->unique();
            // 26 Crockford base32 characters from 130 random bits: the public verification secret.
            $table->char('credential_id', 26)->unique();
            $table->string('holder_name', 120);              // snapshot at issue time
            $table->string('programme_title', 120);
            $table->unsignedSmallInteger('course_version');
            $table->timestamp('issued_at')->useCurrent();
            $table->string('status', 20)->default('valid');
            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason', 500)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'issued_at']);
        });

        Schema::create('certificate_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->constrained()->restrictOnDelete();
            $table->string('action', 20);
            $table->string('reason', 500)->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('certificate_id');
        });

        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 30);
            $table->string('name', 120);
            $table->string('email', 254);
            $table->string('organisation', 160)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('specialization_slug', 80)->nullable();
            $table->string('team_size', 40)->nullable();
            $table->text('message');
            $table->string('status', 20)->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('title', 200);
            $table->string('summary', 400);
            $table->longText('body_md');
            $table->json('tags');
            $table->string('author_name', 120)->default('AMANIQ Academy');
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        // Append-only log of sensitive actions. actor_id NULL means the platform acted automatically.
        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80);
            $table->string('entity_type', 60);
            $table->string('entity_id', 60)->nullable();
            $table->json('details');
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
        });

        Schema::create('platform_settings', function (Blueprint $table) {
            $table->string('key', 80)->primary();
            $table->json('value');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        foreach ([
            "ALTER TABLE badge_definitions ADD CONSTRAINT badges_status_check CHECK (status IN ('active','retired'))",
            "ALTER TABLE certificates ADD CONSTRAINT certs_status_check CHECK (status IN ('valid','revoked'))",
            // A certificate is either valid with no revocation date, or revoked with one.
            "ALTER TABLE certificates ADD CONSTRAINT certs_revocation_check CHECK ((status = 'valid' AND revoked_at IS NULL) OR (status = 'revoked' AND revoked_at IS NOT NULL))",
            "ALTER TABLE certificate_events ADD CONSTRAINT cert_events_action_check CHECK (action IN ('issued','revoked','reinstated'))",
            "ALTER TABLE enquiries ADD CONSTRAINT enquiries_kind_check CHECK (kind IN ('general','corporate','programme_interest'))",
            "ALTER TABLE enquiries ADD CONSTRAINT enquiries_status_check CHECK (status IN ('new','in_progress','closed'))",
            "ALTER TABLE articles ADD CONSTRAINT articles_status_check CHECK (status IN ('draft','published','archived'))",
        ] as $sql) {
            DB::statement($sql);
        }
    }

    public function down(): void
    {
        foreach (['platform_settings', 'audit_events', 'articles', 'enquiries', 'certificate_events', 'certificates', 'badge_awards', 'badge_definitions'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
