<?php

namespace App\Services;

use App\Models\AuditEvent;

/** Records sensitive actions. Call inside the same database transaction as the change. */
final class AuditLogger
{
    public static function record(?int $actorId, string $action, string $entityType, int|string|null $entityId = null, array $details = []): void
    {
        AuditEvent::create([
            'actor_id' => $actorId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId === null ? null : (string) $entityId,
            'details' => $details,
        ]);
    }
}
