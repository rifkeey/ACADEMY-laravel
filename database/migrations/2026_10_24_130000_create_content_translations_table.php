<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per content item and language. The English stays in the content tables; a row
        // holds translated segments, each with the English it was translated from, so a change to
        // the English is detected per segment ("out of date").
        Schema::create('content_translations', function (Blueprint $table) {
            $table->id();
            $table->string('item_type', 20);                 // programme, module, lesson, lab, question, exam, badge, article
            $table->unsignedBigInteger('item_id');
            $table->string('locale', 5);
            $table->json('segments');                        // {path: {source, text}}
            $table->string('status', 20)->default('draft');  // draft | in_review | approved
            $table->boolean('outdated')->default(false);     // the English changed since translation; kept current on every English save
            $table->foreignId('translated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('origin', 20)->default('human'); // human | machine (an imported draft, always reviewed before use)
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->unique(['item_type', 'item_id', 'locale']);
            $table->index(['locale', 'status', 'outdated']);
        });

        Schema::table('exam_attempts', function (Blueprint $table) {
            // Language the attempt's question snapshot was taken in; a past attempt never changes.
            $table->string('locale', 5)->default('en')->after('pass_percent');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', fn (Blueprint $table) => $table->dropColumn('locale'));
        Schema::dropIfExists('content_translations');
    }
};
