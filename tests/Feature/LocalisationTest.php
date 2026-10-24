<?php

namespace Tests\Feature;

use App\Models\LessonProgress;
use App\Models\Specialization;
use App\Models\User;
use App\Services\CheckoutService;
use Database\Seeders\CatalogueSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * English and French interface: URLs, the language switch, saved preferences, and that
 * switching never signs anyone out or touches their progress.
 */
class LocalisationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = CatalogueSeeder::class;

    #[Test]
    public function english_urls_are_unchanged_and_french_pages_live_under_fr(): void
    {
        $this->get('/')->assertOk()->assertSee('<html lang="en-GB">', false)->assertSee('Learn security the way it is actually done.');
        $this->get('/programmes/network-security')->assertOk()->assertSee('Who it is for');

        $this->get('/fr')->assertOk()->assertSee('<html lang="fr">', false)->assertSee('Apprenez la sécurité telle qu’elle se pratique réellement.');
        $fr = $this->get('/fr/programmes/network-security')->assertOk()->assertSee('À qui s’adresse ce programme');
        // Links on a French page stay in French; the switch offers the English equivalent.
        $fr->assertSee('href="'.url('/fr/programmes').'"', false)->assertSee(route('locale.switch', ['locale' => 'en', 'to' => '/fr/programmes/network-security']), false);
    }

    #[Test]
    public function a_guest_choice_is_remembered_with_a_cookie(): void
    {
        $this->get(route('locale.switch', ['locale' => 'fr', 'to' => '/programmes/soc-analyst']))
            ->assertRedirect('/fr/programmes/soc-analyst')->assertCookie('locale', 'fr');

        // Later visits to English public pages follow the saved choice.
        $this->withCookie('locale', 'fr')->get('/about')->assertRedirect('/fr/about');
        $this->withCookie('locale', 'fr')->get('/login')->assertOk()->assertSee('<html lang="fr">', false)->assertSee('Mot de passe oublié');

        // Switching back to English returns to the same page in English.
        $this->withCookie('locale', 'fr')->get(route('locale.switch', ['locale' => 'en', 'to' => '/fr/about']))
            ->assertRedirect('/about')->assertCookie('locale', 'en');
    }

    #[Test]
    public function search_engines_and_new_visitors_are_never_redirected(): void
    {
        // No cookie, French browser: English URLs still show English, so each URL has one language.
        $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9')->get('/')->assertOk()->assertSee('<html lang="en-GB">', false);
    }

    #[Test]
    public function the_switch_only_returns_to_this_site(): void
    {
        foreach (['//evil.example/x', 'https://evil.example', '\\\\evil', 'javascript:alert(1)'] as $to) {
            $this->get(route('locale.switch', ['locale' => 'fr', 'to' => $to]))->assertRedirect('/fr');
        }
        $this->get('/language/de')->assertNotFound();
    }

    #[Test]
    public function a_signed_in_learner_keeps_session_and_progress_when_switching(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $learner = User::factory()->create();
        $soc = Specialization::where('slug', 'soc-analyst')->firstOrFail();
        app(CheckoutService::class)->grantComplimentary($admin, $learner->email, $soc, 'Test');
        $this->actingAs($learner);

        $lesson = \App\Models\Lesson::whereHas('module', fn ($q) => $q->where('course_version_id', $soc->current_version_id))->orderBy('id')->firstOrFail();
        $this->post(route('learn.lesson.complete', [$soc->slug, $lesson->id]))->assertRedirect();
        $before = LessonProgress::where('user_id', $learner->id)->count();

        $this->get(route('locale.switch', ['locale' => 'fr', 'to' => route('learn.course', $soc->slug, false)]))
            ->assertRedirect(route('learn.course', $soc->slug, false));
        $this->assertSame('fr', $learner->fresh()->locale);
        $this->assertAuthenticatedAs($learner);
        $this->assertSame($before, LessonProgress::where('user_id', $learner->id)->count());

        // Signed-in pages keep their URL and follow the saved preference.
        $this->actingAs($learner->fresh())->get(route('dashboard'))->assertOk()->assertSee('<html lang="fr">', false)->assertSee('Vos programmes');
        $this->get(route('learn.course', $soc->slug))->assertOk()->assertSee('Conditions de validation');
    }

    #[Test]
    public function validation_messages_follow_the_language(): void
    {
        $this->withCookie('locale', 'fr')->from('/register')->post('/register', ['email' => 'not-an-email'])
            ->assertSessionHasErrors(['email' => 'Le champ adresse e-mail doit être une adresse e-mail valide.', 'name' => 'Saisissez votre nom complet tel qu’il doit figurer sur les certificats.']);
    }

    #[Test]
    public function a_new_account_keeps_the_visitors_language_and_emails_follow_it(): void
    {
        Notification::fake();
        $this->withCookie('locale', 'fr')->post('/register', [
            'name' => 'Awa Koné', 'email' => 'awa@example.com', 'password' => 'Une-phrase-de-passe-2026', 'password_confirmation' => 'Une-phrase-de-passe-2026', 'terms' => '1',
        ])->assertRedirect();
        $user = User::where('email', 'awa@example.com')->firstOrFail();
        $this->assertSame('fr', $user->locale);
        $this->assertSame('fr', $user->preferredLocale());
        Notification::assertSentTo($user, VerifyEmail::class);

        app()->setLocale($user->preferredLocale());
        $this->assertSame('Confirmez votre adresse e-mail', (new VerifyEmail)->toMail($user)->subject);
        $this->assertSame('Réinitialisez votre mot de passe', (new ResetPassword('token'))->toMail($user)->subject);
    }

    #[Test]
    public function every_translation_key_used_in_the_code_has_french_text(): void
    {
        $fr = json_decode(file_get_contents(lang_path('fr.json')), true, flags: JSON_THROW_ON_ERROR);
        $missing = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views')));
        $paths = array_merge(iterator_to_array($files), iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path()))));
        foreach ($paths as $file) {
            if (! str_ends_with($file, '.php')) {
                continue;
            }
            preg_match_all("/(?:__|trans_choice)\\(\\s*'((?:[^'\\\\]|\\\\.)*)'/", file_get_contents($file), $m);
            foreach ($m[1] as $key) {
                $key = stripslashes($key);
                if (! array_key_exists($key, $fr)) {
                    $missing[] = $key.' ('.basename($file).')';
                }
            }
        }
        $this->assertSame([], array_values(array_unique($missing)), 'Interface text without a French translation');

        // Placeholders must survive translation.
        foreach ($fr as $key => $text) {
            preg_match_all('/:[a-z_]+/', $key, $a);
            preg_match_all('/:[a-z_]+/', $text, $b);
            sort($a[0]);
            sort($b[0]);
            $this->assertSame($a[0], $b[0], "Placeholders differ for: {$key}");
        }
    }
}
