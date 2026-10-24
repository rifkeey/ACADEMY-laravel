<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\LearnController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\VerifyController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
| Public website, once per language. English keeps the original URLs and route names; each
| other language is prefixed (/fr/programmes, route "fr.programmes.index"). Use lroute() to link
| to these pages in the current language.
*/
$publicPages = function () {
    Route::get('/', [PublicController::class, 'home'])->name('home');
    Route::get('/programmes', [CatalogueController::class, 'index'])->name('programmes.index');
    Route::get('/programmes/{specialization}', [CatalogueController::class, 'show'])->name('programmes.show');
    Route::get('/about', [PublicController::class, 'about'])->name('about');
    Route::get('/corporate', [PublicController::class, 'corporate'])->name('corporate');
    Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
    Route::post('/contact', [PublicController::class, 'storeEnquiry'])->middleware('throttle:enquiries')->name('contact.store');
    Route::get('/resources', [PublicController::class, 'resources'])->name('resources.index');
    Route::get('/resources/{article}', [PublicController::class, 'article'])->name('resources.show');
    Route::get('/legal/{doc}', [PublicController::class, 'legal'])->whereIn('doc', ['privacy', 'terms', 'acceptable-use', 'refunds'])->name('legal');

    Route::middleware('throttle:verify')->group(function () {
        Route::get('/verify', [VerifyController::class, 'index'])->name('verify.index');
        Route::post('/verify', [VerifyController::class, 'lookup'])->name('verify.lookup');
        Route::get('/verify/{credentialId}', [VerifyController::class, 'show'])->where('credentialId', '[A-Za-z0-9\-]{1,40}')->name('verify.show');
    });
};
$publicPages();
foreach (array_diff(array_keys(config('academy.locales')), [config('academy.default_locale')]) as $locale) {
    Route::prefix($locale)->name("{$locale}.")->group($publicPages);
}

// EN | FR switcher: saves the choice and returns to the same page in that language.
Route::get('/language/{locale}', [LocaleController::class, 'switch'])->middleware('throttle:60,1')->name('locale.switch');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Payment provider webhooks: no session or CSRF token; authenticated by signature.
Route::post('/webhooks/payments', WebhookController::class)->middleware('throttle:webhooks')->name('webhooks.payments');

/*
| Signed-in learners (email verified)
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/payments', [DashboardController::class, 'payments'])->name('dashboard.payments');
    Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio');
    Route::get('/account', [AccountController::class, 'show'])->name('account');
    Route::get('/account/security', [AccountController::class, 'security'])->middleware('password.confirm')->name('account.security');

    Route::prefix('/learn/{slug}')->where(['slug' => '[a-z0-9-]+'])->group(function () {
        Route::get('/', [LearnController::class, 'course'])->name('learn.course');
        Route::get('/lessons/{lesson}', [LearnController::class, 'lesson'])->whereNumber('lesson')->name('learn.lesson');
        Route::post('/lessons/{lesson}/complete', [LearnController::class, 'complete'])->whereNumber('lesson')->name('learn.lesson.complete');

        Route::get('/labs/{labSlug}', [LabController::class, 'show'])->name('learn.lab');
        Route::post('/labs/{labSlug}/query', [LabController::class, 'query'])->middleware('throttle:kql')->name('learn.lab.query');
        Route::post('/labs/{labSlug}/submit', [LabController::class, 'submit'])->middleware('throttle:lab-submit')->name('learn.lab.submit');

        Route::get('/exam', [ExamController::class, 'show'])->name('exam.show');
        Route::post('/exam/start', [ExamController::class, 'start'])->name('exam.start');
        Route::get('/exam/{attempt}', [ExamController::class, 'take'])->name('exam.take');
        Route::post('/exam/{attempt}/submit', [ExamController::class, 'submit'])->name('exam.submit');
        Route::get('/exam/{attempt}/result', [ExamController::class, 'result'])->name('exam.result');
    });

    Route::get('/checkout/complete', [CheckoutController::class, 'complete'])->name('checkout.complete');
    Route::get('/checkout/sandbox/{reference}', [CheckoutController::class, 'sandbox'])->name('checkout.sandbox');
    Route::post('/checkout/sandbox/{reference}', [CheckoutController::class, 'sandboxPay'])->name('checkout.sandbox.pay');
    Route::get('/checkout/{specialization}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{specialization}', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');

    Route::get('/certificates/{certificate}/pdf', [CertificateController::class, 'pdf'])->whereNumber('certificate')->name('certificates.pdf');
});

/*
| Administration (staff only; every action re-checks its own permission)
*/
Route::middleware(['auth', 'verified', 'staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/role', [Admin\UserController::class, 'role'])->name('users.role');
    Route::put('/users/{user}/status', [Admin\UserController::class, 'status'])->name('users.status');

    Route::get('/programmes', [Admin\ProgrammeController::class, 'index'])->name('programmes.index');
    Route::get('/programmes/{programme}', [Admin\ProgrammeController::class, 'edit'])->name('programmes.edit');
    Route::put('/programmes/{programme}', [Admin\ProgrammeController::class, 'update'])->name('programmes.update');
    Route::put('/programmes/{programme}/publication', [Admin\ProgrammeController::class, 'publication'])->name('programmes.publication');
    Route::post('/programmes/{programme}/versions', [Admin\ProgrammeController::class, 'createDraft'])->name('programmes.versions.store');
    Route::post('/versions/{version}/publish', [Admin\ProgrammeController::class, 'publishVersion'])->name('versions.publish');

    Route::post('/versions/{version}/modules', [Admin\ModuleController::class, 'store'])->name('modules.store');
    Route::put('/modules/{module}', [Admin\ModuleController::class, 'update'])->name('modules.update');
    Route::post('/modules/{module}/move', [Admin\ModuleController::class, 'move'])->name('modules.move');
    Route::put('/modules/{module}/status', [Admin\ModuleController::class, 'status'])->name('modules.status');

    Route::post('/modules/{module}/lessons', [Admin\LessonController::class, 'store'])->name('lessons.store');
    Route::get('/lessons/{lesson}', [Admin\LessonController::class, 'edit'])->name('lessons.edit');
    Route::put('/lessons/{lesson}', [Admin\LessonController::class, 'update'])->name('lessons.update');

    Route::get('/labs', [Admin\LabController::class, 'index'])->name('labs.index');
    Route::get('/labs/{lab}', [Admin\LabController::class, 'edit'])->name('labs.edit');
    Route::put('/labs/{lab}', [Admin\LabController::class, 'update'])->name('labs.update');

    Route::get('/questions', [Admin\QuestionController::class, 'index'])->name('questions.index');
    Route::get('/questions/create', [Admin\QuestionController::class, 'create'])->name('questions.create');
    Route::post('/questions', [Admin\QuestionController::class, 'store'])->name('questions.store');
    Route::post('/questions/status', [Admin\QuestionController::class, 'bulkStatus'])->name('questions.status');
    Route::get('/questions/{question}', [Admin\QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('/questions/{question}', [Admin\QuestionController::class, 'update'])->name('questions.update');

    Route::get('/exams', [Admin\ExamController::class, 'index'])->name('exams.index');
    Route::put('/exams/{exam}', [Admin\ExamController::class, 'update'])->name('exams.update');

    Route::get('/badges', [Admin\BadgeController::class, 'index'])->name('badges.index');
    Route::put('/badges/{badge}', [Admin\BadgeController::class, 'update'])->name('badges.update');

    Route::get('/certificates', [Admin\CertificateController::class, 'index'])->name('certificates.index');
    Route::post('/certificates/{certificate}/revoke', [Admin\CertificateController::class, 'revoke'])->name('certificates.revoke');
    Route::post('/certificates/{certificate}/reinstate', [Admin\CertificateController::class, 'reinstate'])->name('certificates.reinstate');

    Route::get('/pricing', [Admin\PricingController::class, 'index'])->name('pricing.index');
    Route::post('/pricing/{programme}', [Admin\PricingController::class, 'price'])->name('pricing.price');
    Route::post('/discounts', [Admin\PricingController::class, 'storeDiscount'])->name('discounts.store');
    Route::put('/discounts/{discount}', [Admin\PricingController::class, 'toggleDiscount'])->name('discounts.toggle');

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/refund', [Admin\OrderController::class, 'refund'])->name('orders.refund');
    Route::post('/enrolments', [Admin\OrderController::class, 'grant'])->name('enrolments.grant');
    Route::post('/enrolments/{enrolment}/revoke', [Admin\OrderController::class, 'revoke'])->name('enrolments.revoke');

    Route::get('/enquiries', [Admin\EnquiryController::class, 'index'])->name('enquiries.index');
    Route::put('/enquiries/{enquiry}', [Admin\EnquiryController::class, 'update'])->name('enquiries.update');

    Route::get('/articles', [Admin\ArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/create', [Admin\ArticleController::class, 'create'])->name('articles.create');
    Route::post('/articles', [Admin\ArticleController::class, 'store'])->name('articles.store');
    Route::get('/articles/{article}', [Admin\ArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/articles/{article}', [Admin\ArticleController::class, 'update'])->name('articles.update');

    Route::get('/sources', [Admin\SourceController::class, 'index'])->name('sources.index');
    Route::post('/sources', [Admin\SourceController::class, 'store'])->name('sources.store');
    Route::put('/sources/{source}', [Admin\SourceController::class, 'update'])->name('sources.update');

    Route::get('/settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');

    Route::get('/audit', [Admin\AuditController::class, 'index'])->name('audit.index');
});
