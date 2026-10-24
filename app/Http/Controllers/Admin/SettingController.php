<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Small operational settings stored in the database. Secrets never go here: they live in .env. */
class SettingController extends Controller
{
    private const KEYS = ['contact_email', 'support_hours', 'refund_policy_days', 'certificate_signatory', 'verification_name_display'];

    public function edit(): View
    {
        $this->authorize('settings.manage');

        return view('admin.settings.edit', ['settings' => PlatformSetting::whereIn('key', self::KEYS)->pluck('value', 'key')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('settings.manage');
        $data = $request->validate([
            'contact_email' => ['nullable', 'email', 'max:254'],
            'support_hours' => ['nullable', 'string', 'max:120'],
            'refund_policy_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'certificate_signatory' => ['nullable', 'string', 'max:120'],
            'verification_name_display' => ['required', Rule::in(['full', 'initials'])],
        ]);

        DB::transaction(function () use ($request, $data) {
            foreach (self::KEYS as $key) {
                PlatformSetting::updateOrCreate(['key' => $key], ['value' => (string) ($data[$key] ?? ''), 'updated_by' => $request->user()->id]);
            }
            AuditLogger::record($request->user()->id, 'settings.updated', 'platform_settings', null, ['keys' => self::KEYS]);
        });

        return back()->with('success', __('Settings saved.'));
    }
}
