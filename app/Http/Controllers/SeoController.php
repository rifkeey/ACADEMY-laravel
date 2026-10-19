<?php

namespace App\Http\Controllers;

use App\Services\CatalogueService;
use Illuminate\Http\Response;

/** sitemap.xml and robots.txt. Non-production sites are never indexed. */
class SeoController extends Controller
{
    public function sitemap(CatalogueService $catalogue): Response
    {
        $base = config('academy.site_url');
        $urls = array_map(fn ($p) => ['loc' => $base.$p, 'priority' => $p === '' ? '1.0' : '0.7'], ['', '/programmes', '/corporate', '/resources', '/about', '/contact', '/verify']);
        foreach ($catalogue->listProgrammes() as $p) {
            $urls[] = ['loc' => "{$base}/programmes/{$p->slug}", 'priority' => '0.9'];
        }
        foreach ($catalogue->articles(500) as $a) {
            $urls[] = ['loc' => "{$base}/resources/{$a->slug}", 'priority' => '0.6', 'lastmod' => $a->published_at?->toDateString()];
        }
        foreach (['privacy', 'terms', 'acceptable-use', 'refunds'] as $d) {
            $urls[] = ['loc' => "{$base}/legal/{$d}", 'priority' => '0.3'];
        }

        return response()->view('seo.sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $base = config('academy.site_url');
        $body = app()->isProduction()
            ? "User-agent: *\nAllow: /\nDisallow: /dashboard\nDisallow: /learn/\nDisallow: /account\nDisallow: /checkout/\nDisallow: /admin\nDisallow: /certificates/\nDisallow: /verify/\nDisallow: /login\nDisallow: /register\n\nSitemap: {$base}/sitemap.xml\n"
            : "User-agent: *\nDisallow: /\n";

        return response($body)->header('Content-Type', 'text/plain');
    }
}
