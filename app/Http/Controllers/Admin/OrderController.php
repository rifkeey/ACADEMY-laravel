<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RuleViolation;
use App\Http\Controllers\Controller;
use App\Models\Enrolment;
use App\Models\Order;
use App\Models\Specialization;
use App\Services\AuditLogger;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Orders (commerce.manage) and enrolments (enrolments.manage) on one page. */
class OrderController extends Controller
{
    private const ORDER_STATUSES = ['pending', 'paid', 'failed', 'cancelled', 'refunded'];

    public function __construct(private readonly CheckoutService $checkout) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->can('commerce.manage') || $user->can('enrolments.manage'), 403);
        $status = in_array($request->query('status'), self::ORDER_STATUSES, true) ? $request->query('status') : null;
        $q = trim((string) $request->query('q'));
        $byEmail = fn ($w) => $w->whereHas('user', fn ($u) => $u->where('email', 'like', '%'.addcslashes($q, '%_').'%'));

        return view('admin.orders.index', [
            'orders' => $user->can('commerce.manage')
                ? Order::with(['user:id,name,email', 'specialization:id,title', 'payments'])
                    ->when($status, fn ($w) => $w->where('status', $status))
                    ->when($q !== '', fn ($w) => $w->where(fn ($s) => $s->where('reference', $q)->orWhere($byEmail)))
                    ->latest()->paginate(25, ['*'], 'orders')->withQueryString()
                : null,
            'enrolments' => $user->can('enrolments.manage')
                ? Enrolment::with(['user:id,name,email', 'specialization:id,title', 'courseVersion:id,version'])
                    ->when($q !== '', $byEmail)
                    ->latest('enrolled_at')->paginate(25, ['*'], 'enrolments')->withQueryString()
                : null,
            'programmes' => Specialization::orderBy('sort_order')->get(['id', 'title']),
            'status' => $status, 'q' => $q,
        ]);
    }

    public function refund(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('commerce.manage');
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']])['reason'];
        // Records a refund already made in the payment provider's dashboard and removes access.
        $this->checkout->markRefunded($request->user(), $order, $reason);

        return back()->with('success', __('Order :reference marked as refunded and its enrolment revoked.', ['reference' => $order->reference]));
    }

    public function grant(Request $request): RedirectResponse
    {
        $this->authorize('enrolments.manage');
        $data = $request->validate([
            'email' => ['required', 'email', 'max:254'],
            'specialization_id' => ['required', 'integer', 'exists:specializations,id'],
            'note' => ['required', 'string', 'min:5', 'max:300'],
        ]);
        $enrolment = $this->checkout->grantComplimentary($request->user(), strtolower($data['email']), Specialization::findOrFail($data['specialization_id']), $data['note']);

        return back()->with('success', __('Complimentary enrolment granted (#:id).', ['id' => $enrolment->id]));
    }

    public function revoke(Request $request, Enrolment $enrolment): RedirectResponse
    {
        $this->authorize('enrolments.manage');
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']])['reason'];

        DB::transaction(function () use ($request, $enrolment, $reason) {
            $changed = Enrolment::whereKey($enrolment->id)->where('status', 'active')->update(['status' => 'revoked', 'updated_at' => now()]);
            if (! $changed) {
                throw new RuleViolation(__('This enrolment is already revoked.'));
            }
            AuditLogger::record($request->user()->id, 'enrolment.revoked', 'enrolment', $enrolment->id, ['reason' => $reason]);
        });

        return back()->with('success', __('Enrolment revoked. The learner can no longer open the course.'));
    }
}
