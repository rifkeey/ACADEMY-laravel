<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Resource articles. Publishing (making an article public) also needs content.publish. */
class ArticleController extends Controller
{
    public function index(): View
    {
        $this->authorize('content.edit');

        return view('admin.articles.index', ['articles' => Article::latest('updated_at')->paginate(25)]);
    }

    public function create(): View
    {
        $this->authorize('content.edit');

        return view('admin.articles.form', ['article' => new Article(['status' => 'draft', 'author_name' => 'AMANIQ Academy', 'tags' => []])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('content.edit');
        $data = $this->validated($request, null);
        $article = DB::transaction(function () use ($request, $data) {
            $article = new Article;
            $this->save($request, $article, $data);
            AuditLogger::record($request->user()->id, 'article.created', 'article', $article->id, ['slug' => $article->slug]);

            return $article;
        });

        return redirect()->route('admin.articles.edit', $article)->with('success', 'Article created.');
    }

    public function edit(Article $article): View
    {
        $this->authorize('content.edit');

        return view('admin.articles.form', ['article' => $article]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $this->authorize('content.edit');
        $data = $this->validated($request, $article);
        DB::transaction(function () use ($request, $article, $data) {
            $this->save($request, $article, $data);
            AuditLogger::record($request->user()->id, 'article.updated', 'article', $article->id, ['status' => $article->status]);
        });

        return redirect()->route('admin.articles.edit', $article)->with('success', 'Article saved.');
    }

    private function validated(Request $request, ?Article $article): array
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('articles', 'slug')->ignore($article?->id)],
            'title' => ['required', 'string', 'max:200'],
            'summary' => ['required', 'string', 'max:400'],
            'body_md' => ['required', 'string', 'max:200000'],
            'tags' => ['nullable', 'string', 'max:300'],
            'author_name' => ['required', 'string', 'max:120'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
        ]);
        // Changing whether an article is public is a publishing decision.
        if ($data['status'] !== ($article?->status ?? 'draft') && ($data['status'] === 'published' || $article?->status === 'published')) {
            $this->authorize('content.publish');
        }

        return $data;
    }

    private function save(Request $request, Article $article, array $data): void
    {
        $tags = array_slice(array_values(array_filter(array_map(fn ($t) => mb_substr(trim($t), 0, 40), explode(',', $data['tags'] ?? '')))), 0, 10);
        $article->forceFill([
            'slug' => $data['slug'], 'title' => $data['title'], 'summary' => $data['summary'], 'body_md' => $data['body_md'],
            'tags' => $tags, 'author_name' => $data['author_name'], 'status' => $data['status'],
            'published_at' => $data['status'] === 'published' ? ($article->published_at ?? now()) : $article->published_at,
        ])->save();
    }
}
