<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Development-only demo accounts. Refuses to run in production.
 * The passwords below exist only for local testing and are never valid anywhere else.
 */
class DevAccountsSeeder extends Seeder
{
    public const ACCOUNTS = [
        ['email' => 'admin@amaniq.test', 'password' => 'Admin-Dev-Only-2026!', 'name' => 'Dev Administrator', 'role' => 'admin'],
        ['email' => 'instructor@amaniq.test', 'password' => 'Instructor-Dev-Only-2026!', 'name' => 'Dev Instructor', 'role' => 'instructor'],
        ['email' => 'learner@amaniq.test', 'password' => 'Learner-Dev-Only-2026!', 'name' => 'Ada Learner', 'role' => 'learner'],
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('DevAccountsSeeder must never run in production.');
        }
        foreach (self::ACCOUNTS as $a) {
            $user = User::firstOrNew(['email' => $a['email']]);
            $user->fill(['name' => $a['name'], 'password' => $a['password']]);
            $user->forceFill(['role' => $a['role'], 'email_verified_at' => now()])->save();
        }
    }
}
