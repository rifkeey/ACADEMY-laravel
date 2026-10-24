<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnquiryRequest;
use App\Models\Article;
use App\Models\Enquiry;
use App\Models\LegalPage;
use App\Services\CatalogueService;
use App\Services\TranslationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Marketing and information pages. */
class PublicController extends Controller
{
    public function __construct(private readonly CatalogueService $catalogue) {}

    public function home(): View
    {
        return view('public.home', [
            'programmes' => $this->catalogue->listProgrammes(),
            'articles' => $this->catalogue->articles(3),
        ]);
    }

    public function about(): View
    {
        return view('public.about');
    }

    public function corporate(): View
    {
        return view('public.corporate', ['programmes' => $this->catalogue->listProgrammes()]);
    }

    public function contact(Request $request): View
    {
        return view('public.contact', [
            'programmes' => $this->catalogue->listProgrammes(),
            'kind' => in_array($request->query('kind'), ['corporate', 'programme_interest'], true) ? $request->query('kind') : 'general',
            'programme' => (string) $request->query('programme', ''),
            'contactEmail' => $this->catalogue->setting('contact_email'),
            'supportHours' => $this->catalogue->setting('support_hours'),
        ]);
    }

    public function storeEnquiry(EnquiryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['website']);
        Enquiry::create($data);

        return back()->with('success', __('Thank you. Your enquiry has been received and a member of the AMANIQ Academy team will reply by email.'));
    }

    public function resources(): View
    {
        return view('public.resources', ['articles' => $this->catalogue->articles()]);
    }

    public function article(Article $article): View
    {
        abort_unless($article->status === 'published', 404);
        app(TranslationService::class)->localise($article);

        return view('public.article', ['article' => $article]);
    }

    public function legal(string $doc): View
    {
        $page = LegalPage::forSlug($doc);
        abort_unless($page, 404);
        app(TranslationService::class)->localise($page);

        return view('public.legal', ['page' => $page]);
    }
}
