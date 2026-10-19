<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pricing, orders, payments and the enrolment entitlements they produce, plus lesson progress.
 * Money is always integer minor units (kobo for NGN) with an explicit ISO 4217 currency code.
 */
return new class extends Migration
{
    public function up(): void
    {
        // A price for a programme in one currency. Changing a price adds a row; history is kept.
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialization_id')->constrained()->restrictOnDelete();
            $table->char('currency', 3);
            $table->unsignedBigInteger('amount_minor');
            $table->boolean('active')->default(true);
            $table->timestamp('valid_from')->useCurrent();
            $table->timestamp('valid_until')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['specialization_id', 'currency', 'active']);
        });

        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();          // stored upper-case; collation is case-insensitive
            $table->string('description', 200)->nullable();
            $table->unsignedTinyInteger('percent_off')->nullable();
            $table->unsignedBigInteger('amount_off_minor')->nullable();
            $table->char('currency', 3)->nullable();
            $table->foreignId('specialization_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('redemptions')->default(0);
            $table->timestamp('valid_from')->useCurrent();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();      // public, random; never the numeric id
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('specialization_id')->constrained()->restrictOnDelete();
            $table->foreignId('offer_id')->constrained()->restrictOnDelete();
            $table->foreignId('discount_id')->nullable()->constrained()->restrictOnDelete();
            $table->char('currency', 3);
            $table->unsignedBigInteger('subtotal_minor');
            $table->unsignedBigInteger('discount_minor')->default(0);
            $table->unsignedBigInteger('total_minor');
            $table->string('status', 20)->default('pending');
            $table->string('provider', 20);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->string('provider', 20);
            $table->string('provider_reference', 120);
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3);
            $table->string('status', 20);
            $table->string('channel', 40)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_reference']);   // a provider transaction is recorded once
        });

        Schema::create('payment_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 20);
            $table->string('event_key', 190);
            $table->string('event_type', 80);
            $table->json('payload');
            $table->string('result', 40)->nullable();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();

            $table->unique(['provider', 'event_key']);            // duplicate deliveries are ignored
        });

        // Access to a programme. One per learner per programme; pinned to the version they enrolled in.
        Schema::create('enrolments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('specialization_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_version_id')->constrained()->restrictOnDelete();
            $table->string('source', 20);
            $table->foreignId('order_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('active');
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'specialization_id']);
            $table->index(['course_version_id', 'status']);
        });

        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('lesson_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrolment_id')->constrained()->restrictOnDelete();
            $table->timestamp('completed_at')->useCurrent();

            $table->primary(['user_id', 'lesson_id']);              // completing twice is a no-op
            $table->index('enrolment_id');
        });

        foreach ([
            "ALTER TABLE offers ADD CONSTRAINT offers_currency_check CHECK (currency REGEXP '^[A-Z]{3}$')",
            'ALTER TABLE discounts ADD CONSTRAINT discounts_kind_check CHECK ((percent_off IS NOT NULL AND amount_off_minor IS NULL AND percent_off BETWEEN 1 AND 100) OR (percent_off IS NULL AND amount_off_minor IS NOT NULL AND amount_off_minor > 0 AND currency IS NOT NULL))',
            "ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('pending','paid','failed','cancelled','refunded'))",
            'ALTER TABLE orders ADD CONSTRAINT orders_totals_check CHECK (total_minor = subtotal_minor - discount_minor)',
            "ALTER TABLE payments ADD CONSTRAINT payments_status_check CHECK (status IN ('success','failed','refunded'))",
            "ALTER TABLE enrolments ADD CONSTRAINT enrolments_source_check CHECK (source IN ('purchase','complimentary'))",
            "ALTER TABLE enrolments ADD CONSTRAINT enrolments_status_check CHECK (status IN ('active','revoked'))",
        ] as $sql) {
            DB::statement($sql);
        }
    }

    public function down(): void
    {
        foreach (['lesson_progress', 'enrolments', 'payment_webhook_events', 'payments', 'orders', 'discounts', 'offers'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
