<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;

/**
 * The same answer whether or not an account exists for the email (or a reset was requested very
 * recently), so the "forgot password" form cannot be used to find out who has an account.
 */
class PasswordResetLinkResponse implements FailedPasswordResetLinkRequestResponse, SuccessfulPasswordResetLinkRequestResponse
{
    public const MESSAGE = 'If an account exists for that email address, we have sent a link to reset the password. Check your inbox and spam folder.';

    public function __construct(?string $status = null) {}   // Fortify passes the broker status; it is deliberately ignored

    public function toResponse($request)
    {
        return $request->wantsJson()
            ? new JsonResponse(['message' => self::MESSAGE], 200)
            : back()->with('status', self::MESSAGE);
    }
}
