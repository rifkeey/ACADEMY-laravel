<?php

namespace App\Support;

/**
 * Role-based access control, defined in code so it is reviewed and versioned with the app.
 * Every permission is also registered as a Laravel Gate (see AppServiceProvider), so controllers
 * use the standard `$this->authorize('content.edit')` / `@can('content.edit')`.
 */
final class Permissions
{
    public const ROLES = ['learner', 'instructor', 'admin'];

    public const ALL = [
        'admin.access',        // open the admin area
        'content.edit',        // programmes, modules, lessons, labs, sources, articles
        'content.publish',     // publish versions and programmes
        'questions.edit',      // question bank
        'exams.configure',
        'users.manage',        // roles and account status
        'enrolments.manage',   // complimentary enrolments, revocations
        'commerce.manage',     // pricing, discounts, orders
        'credentials.manage',  // badges, certificate revocation
        'enquiries.manage',
        'settings.manage',
        'audit.read',
    ];

    private const MATRIX = [
        'learner' => [],
        'instructor' => ['admin.access', 'content.edit', 'questions.edit'],
        'admin' => self::ALL,
    ];

    public static function allows(?string $role, string $permission): bool
    {
        return $role !== null && in_array($permission, self::MATRIX[$role] ?? [], true);
    }

    public static function isRole(mixed $value): bool
    {
        return is_string($value) && in_array($value, self::ROLES, true);
    }
}
