<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    private const STATUSES = ['new', 'in_progress', 'closed'];

    public function index(Request $request): View
    {
        $this->authorize('enquiries.manage');
        $status = in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : null;

        return view('admin.enquiries.index', [
            'enquiries' => Enquiry::when($status, fn ($w) => $w->where('status', $status))->latest()->paginate(20)->withQueryString(),
            'status' => $status,
            'counts' => Enquiry::groupBy('status')->selectRaw('status, COUNT(*) n')->pluck('n', 'status'),
        ]);
    }

    public function update(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $this->authorize('enquiries.manage');
        $data = $request->validate(['status' => ['required', Rule::in(self::STATUSES)], 'notes' => ['nullable', 'string', 'max:4000']]);
        $enquiry->forceFill($data)->save();
        AuditLogger::record($request->user()->id, 'enquiry.updated', 'enquiry', $enquiry->id, ['status' => $data['status']]);

        return back()->with('success', __('Enquiry updated.'));
    }
}
