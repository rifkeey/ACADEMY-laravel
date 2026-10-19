<?php

namespace App\Providers;

use App\Models\User;
use App\Payments\PaymentProvider;
use App\Payments\PaymentProviderFactory;
use App\Support\Permissions;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // The active payment provider is chosen from config; see App\Payments.
        $this->app->singleton(PaymentProvider::class, fn () => PaymentProviderFactory::make());
    }

    public function boot(): void
    {
        $this->guardProductionConfiguration();

        // Catch lazy-loading and silently discarded attributes during development and tests.
        Model::shouldBeStrict(! $this->app->isProduction());

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Password policy for registration and resets.
        Password::defaults(fn () => Password::min(10)->max(128)->uncompromised(3));

        // Every permission in the matrix becomes a Gate: $this->authorize('content.edit').
        foreach (Permissions::ALL as $permission) {
            Gate::define($permission, fn (User $user) => $user->isActive() && Permissions::allows($user->role, $permission));
        }

        $this->registerRateLimiters();
    }

    /** Refuse to boot with unsafe settings in production rather than fail open. */
    private function guardProductionConfiguration(): void
    {
        if (! $this->app->isProduction()) {
            return;
        }
        $problems = [];
        if (config('app.debug')) {
            $problems[] = 'APP_DEBUG must be false';
        }
        if (! str_starts_with(config('academy.site_url'), 'https://')) {
            $problems[] = 'ACADEMY_SITE_URL must use https';
        }
        if (config('academy.payments.provider') === 'sandbox') {
            $problems[] = 'the sandbox payment provider is not permitted in production';
        }
        if (str_starts_with((string) config('academy.payments.paystack.secret_key'), 'sk_test_')) {
            $problems[] = 'a Paystack test key is configured in production';
        }
        if ($problems) {
            throw new RuntimeException('Unsafe production configuration: '.implode('; ', $problems).'.');
        }
    }

    private function registerRateLimiters(): void
    {
        RateLimiter::for('enquiries', fn (Request $r) => Limit::perHour(5)->by($r->ip()));
        RateLimiter::for('checkout', fn (Request $r) => Limit::perHour(10)->by($r->user()?->id ?: $r->ip()));
        RateLimiter::for('lab-submit', fn (Request $r) => Limit::perHour(30)->by($r->user()?->id.'|'.(string) $r->route('labSlug')));
        RateLimiter::for('kql', fn (Request $r) => Limit::perMinute(60)->by($r->user()?->id ?: $r->ip()));
        RateLimiter::for('verify', fn (Request $r) => Limit::perMinute(30)->by($r->ip()));
        RateLimiter::for('webhooks', fn (Request $r) => Limit::perMinute(120)->by($r->ip()));
    }
}
