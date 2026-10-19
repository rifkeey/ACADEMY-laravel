<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\Certificate;
use App\Models\Enquiry;
use App\Models\Enrolment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $this->authorize('admin.access');
        $user = auth()->user();

        return view('admin.dashboard', [
            'stats' => [
                'learners' => User::where('role', 'learner')->count(),
                'enrolments' => Enrolment::where('status', 'active')->count(),
                'certificates' => Certificate::where('status', 'valid')->count(),
                'enquiries' => Enquiry::where('status', 'new')->count(),
            ],
            // Revenue is summed per currency in minor units; only shown to those who manage sales.
            'revenue' => $user->can('commerce.manage')
                ? Order::where('status', 'paid')->groupBy('currency')->select('currency', DB::raw('SUM(total_minor) as total'), DB::raw('COUNT(*) as orders'))->get()
                : collect(),
            'recentOrders' => $user->can('commerce.manage') ? Order::with(['user:id,name,email', 'specialization:id,title'])->latest()->limit(8)->get() : collect(),
            'recentAudit' => $user->can('audit.read') ? AuditEvent::with('actor:id,name')->latest('created_at')->limit(10)->get() : collect(),
        ]);
    }
}
