<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

/**
 * A legal page (privacy, terms, acceptable use, refunds). The English comes from
 * database/content/legal.json, which stays its source: syncing updates the English, and any
 * change marks the French out of date until it is reviewed again.
 */
class LegalPage extends Model
{
    use Translatable;

    public const SLUGS = ['privacy', 'terms', 'acceptable-use', 'refunds'];

    /** Mass assignment is used only by trusted server code (syncFromFile), never with raw request input. */
    protected $guarded = ['id'];

    public static function syncFromFile(): void
    {
        $docs = json_decode(file_get_contents(database_path('content/legal.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach ($docs as $slug => $doc) {
            $page = self::firstOrNew(['slug' => $slug]);
            $page->fill(['title' => $doc['title'], 'summary' => $doc['summary'], 'body_md' => $doc['body']]);
            if ($page->isDirty()) {
                $page->save();
            }
        }
    }

    /** The page by slug, syncing from the file first if it has never been loaded (a fresh install). */
    public static function forSlug(string $slug): ?self
    {
        if (! in_array($slug, self::SLUGS, true)) {
            return null;
        }
        if (! self::where('slug', $slug)->exists()) {
            self::syncFromFile();
        }

        return self::where('slug', $slug)->first();
    }
}
