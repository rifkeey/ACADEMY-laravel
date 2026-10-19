<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** An issued certificate. Status changes only through CredentialService (revoke/reinstate). */
class Certificate extends Model
{
    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(CertificateEvent::class);
    }
}
