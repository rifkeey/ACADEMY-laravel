<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Read-only, append-only record of sensitive actions. */
class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('audit.read');
        $action = preg_replace('/[^a-z_.]/', '', (string) $request->query('action'));
        $entity = preg_replace('/[^a-z_]/', '', (string) $request->query('entity'));

        return view('admin.audit.index', [
            'events' => AuditEvent::with('actor:id,name,email')
                ->when($action !== '', fn ($w) => $w->where('action', 'like', $action.'%'))
                ->when($entity !== '', fn ($w) => $w->where('entity_type', $entity))
                ->latest('created_at')->latest('id')->simplePaginate(50)->withQueryString(),
            'action' => $action, 'entity' => $entity,
            'entities' => AuditEvent::distinct()->orderBy('entity_type')->pluck('entity_type'),
        ]);
    }
}
