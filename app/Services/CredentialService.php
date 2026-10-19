<?php

namespace App\Services;

use App\Models\BadgeAward;
use App\Models\BadgeDefinition;
use App\Models\Certificate;
use App\Models\CertificateEvent;
use App\Models\Enrolment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Support\CredentialIds;
use Illuminate\Support\Facades\DB;

/**
 * Badges and certificates: idempotent issuance, revocation and public verification.
 *
 * Certificates are issued only here, only after a full requirement check against the database,
 * and the record is committed before any PDF can be downloaded.
 */
final class CredentialService
{
    public function __construct(private readonly LearningService $learning) {}

    public static function verificationUrl(string $credentialId): string
    {
        return config('academy.site_url').'/verify/'.$credentialId;
    }

    /**
     * Issues the badge and certificate if — and only if — every requirement is met.
     * Repeated or concurrent calls never create duplicates: the enrolment row is locked and the
     * unique indexes on enrolment_id are the final safeguard.
     */
    public function issueIfEligible(User $user, ?Enrolment $enrolment): array
    {
        if (! $enrolment || $enrolment->user_id !== $user->id) {
            return ['issued' => false, 'reason' => 'no-active-enrolment'];
        }

        return DB::transaction(function () use ($user, $enrolment) {
            $enrolment = Enrolment::whereKey($enrolment->id)->lockForUpdate()->first();
            if ($enrolment->status !== 'active') {
                return ['issued' => false, 'reason' => 'no-active-enrolment'];
            }
            if ($existing = Certificate::where('enrolment_id', $enrolment->id)->first()) {
                return ['issued' => false, 'reason' => 'already-issued', 'certificate' => $existing];
            }
            $requirements = $this->learning->computeRequirements($enrolment);
            if (! $requirements['complete']) {
                return ['issued' => false, 'reason' => 'requirements-incomplete', 'requirements' => $requirements];
            }

            $spec = $enrolment->specialization()->first();
            $version = $enrolment->courseVersion()->first();
            $exam = Exam::where('course_version_id', $version->id)->first();
            $passedAttempt = ExamAttempt::where('enrolment_id', $enrolment->id)->where('exam_id', $exam->id)->where('passed', true)->latest('submitted_at')->first();

            $certificate = Certificate::create([
                'user_id' => $user->id, 'enrolment_id' => $enrolment->id, 'specialization_id' => $spec->id,
                'course_version_id' => $version->id, 'exam_attempt_id' => $passedAttempt->id,
                'certificate_number' => CredentialIds::certificateNumber($spec->code),
                'credential_id' => CredentialIds::credentialId(),
                'holder_name' => trim($user->name) !== '' ? trim($user->name) : $user->email,
                'programme_title' => $spec->title, 'course_version' => $version->version, 'issued_at' => now(),
            ]);
            // Retired badge designs are no longer awarded; the certificate is still issued.
            if ($badge = BadgeDefinition::where('specialization_id', $spec->id)->where('status', 'active')->first()) {
                BadgeAward::create([
                    'user_id' => $user->id, 'badge_definition_id' => $badge->id, 'enrolment_id' => $enrolment->id,
                    'course_version_id' => $version->id, 'credential_id' => $certificate->credential_id,
                ]);
            }
            $enrolment->forceFill(['completed_at' => now()])->save();
            CertificateEvent::create(['certificate_id' => $certificate->id, 'action' => 'issued']);
            AuditLogger::record(null, 'certificate.issued', 'certificate', $certificate->id, ['user_id' => $user->id, 'programme' => $spec->slug]);

            return ['issued' => true, 'certificate' => $certificate];
        });
    }

    public function revoke(User $actor, Certificate $certificate, string $reason): bool
    {
        return DB::transaction(function () use ($actor, $certificate, $reason) {
            $changed = Certificate::whereKey($certificate->id)->where('status', 'valid')
                ->update(['status' => 'revoked', 'revoked_at' => now(), 'revocation_reason' => $reason]);
            if ($changed) {
                CertificateEvent::create(['certificate_id' => $certificate->id, 'action' => 'revoked', 'reason' => $reason, 'actor_id' => $actor->id]);
                AuditLogger::record($actor->id, 'certificate.revoked', 'certificate', $certificate->id, ['reason' => $reason]);
            }

            return (bool) $changed;
        });
    }

    public function reinstate(User $actor, Certificate $certificate, string $reason): bool
    {
        return DB::transaction(function () use ($actor, $certificate, $reason) {
            $changed = Certificate::whereKey($certificate->id)->where('status', 'revoked')
                ->update(['status' => 'valid', 'revoked_at' => null, 'revocation_reason' => null]);
            if ($changed) {
                CertificateEvent::create(['certificate_id' => $certificate->id, 'action' => 'reinstated', 'reason' => $reason, 'actor_id' => $actor->id]);
                AuditLogger::record($actor->id, 'certificate.reinstated', 'certificate', $certificate->id, ['reason' => $reason]);
            }

            return (bool) $changed;
        });
    }

    /**
     * Public verification. Returns only what a verifier needs: never email, account or exam data.
     *
     * @return array{status: 'valid'|'revoked'|'not_found', holderName?: string, programmeTitle?: string,
     *               certificateNumber?: string, issuedAt?: mixed, revokedAt?: mixed, courseVersion?: int, issuer?: string}
     */
    public function verify(string $credentialId): array
    {
        $id = strtoupper(trim($credentialId));
        if (! preg_match(CredentialIds::PATTERN, $id)) {
            return ['status' => 'not_found'];
        }
        $c = Certificate::where('credential_id', $id)->first();
        if (! $c) {
            return ['status' => 'not_found'];
        }
        $initialsOnly = PlatformSetting::find('verification_name_display')?->value === 'initials';

        return [
            'status' => $c->status === 'revoked' ? 'revoked' : 'valid',
            'holderName' => $initialsOnly ? self::initials($c->holder_name) : $c->holder_name,
            'programmeTitle' => $c->programme_title,
            'certificateNumber' => $c->certificate_number,
            'issuedAt' => $c->issued_at,
            'revokedAt' => $c->revoked_at,
            'courseVersion' => $c->course_version,
            'issuer' => config('academy.issuer'),
        ];
    }

    private static function initials(string $full): string
    {
        $parts = preg_split('/\s+/u', trim($full));
        if (count($parts) < 2) {
            return $full;
        }

        return $parts[0].' '.implode(' ', array_map(fn ($p) => mb_substr($p, 0, 1).'.', array_slice($parts, 1)));
    }
}
