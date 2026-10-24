<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Legal pages as records so they can be translated and reviewed like other content.
        // database/content/legal.json stays the source of the English (see LegalPage::syncFromFile).
        Schema::create('legal_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();
            $table->string('title', 200);
            $table->string('summary', 400);
            $table->longText('body_md');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_pages');
    }
};
