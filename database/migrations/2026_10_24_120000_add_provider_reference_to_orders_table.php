<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // The provider's own reference for this payment (GeniusPay issues MTX-…; Paystack uses ours).
            $table->string('provider_reference', 120)->nullable()->after('provider');
            // Language the learner checked out in, for payment messages.
            $table->string('locale', 5)->nullable()->after('provider_reference');
            $table->unique(['provider', 'provider_reference']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['provider', 'provider_reference']);
            $table->dropColumn(['provider_reference', 'locale']);
        });
    }
};
