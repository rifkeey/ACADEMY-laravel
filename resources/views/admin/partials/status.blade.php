{{-- Coloured pill for any status value used in the admin tables. --}}
@php
    $tone = match ($value) {
        'published', 'paid', 'valid', 'active', 'approved', 'verified', 'complete', 'success', 'closed' => 'success',
        'pending', 'draft', 'in_progress', 'pending_review', 'starter', 'in_development' => 'warning',
        'failed', 'revoked', 'suspended', 'refunded', 'unavailable', 'cancelled' => 'danger',
        'new' => 'accent',
        default => 'neutral',
    };
@endphp
<x-ui.pill :tone="$tone">{{ __(str_replace('_', ' ', ucfirst($value))) }}</x-ui.pill>
