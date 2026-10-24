<?php

namespace App\Support;

/** Display labels for stored codes, in the current language. */
final class Labels
{
    /** "admin" → "Administrator" (not "Admin", which names the admin area). */
    public static function role(string $role): string
    {
        return match ($role) {
            'learner' => __('Learner'),
            'instructor' => __('Instructor'),
            'admin' => __('Administrator'),
            default => $role,
        };
    }
}
