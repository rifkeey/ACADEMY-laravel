<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Learners, instructors and administrators. Roles are changed only by administrators.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);                 // full name, printed on certificates
            $table->string('email', 254)->unique();      // utf8mb4_unicode_ci makes this case-insensitive
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role', 20)->default('learner')->index();
            $table->string('status', 20)->default('active');
            $table->string('country', 2)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('learner','instructor','admin'))");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('active','suspended'))");

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Database-backed sessions: works on shared hosting and lets suspended users be signed out.
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
