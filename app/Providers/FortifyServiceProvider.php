<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\PasswordResetLinkResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;

/** Laravel Fortify: session authentication with our own Blade views. */
class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Identical response for known and unknown emails on "forgot password" (no account enumeration).
        $this->app->bind(FailedPasswordResetLinkRequestResponse::class, PasswordResetLinkResponse::class);
        $this->app->bind(SuccessfulPasswordResetLinkRequestResponse::class, PasswordResetLinkResponse::class);
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // Suspended accounts cannot sign in. A generic message avoids revealing which emails exist.
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', mb_strtolower(trim((string) $request->input('email'))))->first();
            if (! $user || ! Hash::check((string) $request->input('password'), $user->password)) {
                return null;
            }
            if (! $user->isActive()) {
                throw ValidationException::withMessages(['email' => 'This account is suspended. Contact AMANIQ Academy support.']);
            }

            return $user;
        });

        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', ['request' => $request]));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));

        // Per email+IP and per IP, so one attacker cannot spray many accounts from one address.
        RateLimiter::for('login', function (Request $request) {
            $email = Str::transliterate(Str::lower((string) $request->input(Fortify::username())));

            return [Limit::perMinute(5)->by($email.'|'.$request->ip()), Limit::perMinute(20)->by($request->ip())];
        });
        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));
    }
}
