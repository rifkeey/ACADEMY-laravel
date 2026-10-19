<?php

namespace App\Models;

use App\Support\Permissions;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * An account. `role` and `status` are deliberately NOT mass-assignable: only administrator
 * actions change them, through explicit assignments that are audited.
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = ['name', 'email', 'password', 'country'];

    // Same defaults as the database columns, so a freshly created user has them in memory too.
    protected $attributes = ['role' => 'learner', 'status' => 'active'];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Staff can reach the admin area (instructors with a subset of permissions, admins with all). */
    public function isStaff(): bool
    {
        return Permissions::allows($this->role, 'admin.access');
    }
}
