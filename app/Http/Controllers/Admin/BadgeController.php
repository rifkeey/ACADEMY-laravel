<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BadgeAward;
use App\Models\BadgeDefinition;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Programme badges. Badges already awarded keep their credential id; retiring stops new awards. */
class BadgeController extends Controller
{
    public function index(): View
    {
        $this->authorize('credentials.manage');

        return view('admin.badges.index', [
            'badges' => BadgeDefinition::with('specialization:id,title')->orderBy('specialization_id')->get(),
            'awarded' => BadgeAward::groupBy('badge_definition_id')->selectRaw('badge_definition_id, COUNT(*) n')->pluck('n', 'badge_definition_id'),
        ]);
    }

    public function update(Request $request, BadgeDefinition $badge): RedirectResponse
    {
        $this->authorize('credentials.manage');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:600'],
            'criteria' => ['required', 'string', 'max:600'],
            'hue' => ['required', 'integer', 'min:0', 'max:359'],
            'icon' => ['required', Rule::in(['radar', 'crosshair', 'scale', 'network', 'cloud', 'shield-check'])],
            'status' => ['required', Rule::in(['active', 'retired'])],
        ]);

        DB::transaction(function () use ($request, $badge, $data) {
            $badge->forceFill($data)->save();
            AuditLogger::record($request->user()->id, 'badge.updated', 'badge_definition', $badge->id, $badge->getChanges());
        });

        return back()->with('success', 'Badge saved.');
    }
}
