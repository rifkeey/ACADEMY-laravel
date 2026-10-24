<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RuleViolation;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Account roles and status. Nobody can change their own role or suspend themselves. */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('users.manage');
        $q = trim((string) $request->query('q'));
        $role = $request->query('role');

        $users = User::query()
            ->when($q !== '', fn ($w) => $w->where(fn ($s) => $s->where('email', 'like', '%'.addcslashes($q, '%_').'%')->orWhere('name', 'like', '%'.addcslashes($q, '%_').'%')))
            ->when(Permissions::isRole($role), fn ($w) => $w->where('role', $role))
            ->withCount('enrolments')
            ->orderByDesc('id')
            ->paginate(25)->withQueryString();

        return view('admin.users.index', ['users' => $users, 'q' => $q, 'role' => $role]);
    }

    public function role(Request $request, User $user): RedirectResponse
    {
        $this->authorize('users.manage');
        $data = $request->validate(['role' => ['required', Rule::in(Permissions::ROLES)]]);
        $this->guardSelf($request, $user);

        DB::transaction(function () use ($request, $user, $data) {
            $locked = User::whereKey($user->id)->lockForUpdate()->first();
            if ($locked->role === 'admin' && $data['role'] !== 'admin') {
                $this->assertAnotherActiveAdmin($locked);
            }
            $from = $locked->role;
            $locked->forceFill(['role' => $data['role']])->save();   // role is never mass-assignable
            AuditLogger::record($request->user()->id, 'user.role_changed', 'user', $locked->id, ['email' => $locked->email, 'from' => $from, 'to' => $data['role']]);
        });

        return back()->with('success', __('Role updated for :email.', ['email' => $user->email]));
    }

    public function status(Request $request, User $user): RedirectResponse
    {
        $this->authorize('users.manage');
        $data = $request->validate(['status' => ['required', Rule::in(['active', 'suspended'])], 'reason' => ['nullable', 'string', 'max:500']]);
        $this->guardSelf($request, $user);

        DB::transaction(function () use ($request, $user, $data) {
            $locked = User::whereKey($user->id)->lockForUpdate()->first();
            if ($locked->role === 'admin' && $data['status'] === 'suspended') {
                $this->assertAnotherActiveAdmin($locked);
            }
            $locked->forceFill(['status' => $data['status']])->save();
            if ($data['status'] === 'suspended') {
                // End their sessions immediately (database session driver).
                DB::table('sessions')->where('user_id', $locked->id)->delete();
            }
            AuditLogger::record($request->user()->id, 'user.status_changed', 'user', $locked->id, ['email' => $locked->email, 'status' => $data['status'], 'reason' => $data['reason'] ?? null]);
        });

        return back()->with('success', __('Account :email is now :status.', ['email' => $user->email, 'status' => __($data['status'])]));
    }

    private function guardSelf(Request $request, User $user): void
    {
        if ($request->user()->is($user)) {
            throw new RuleViolation(__('You cannot change your own role or status. Ask another administrator.'));
        }
    }

    /** Keeps at least one active administrator so the platform can never be locked out. */
    private function assertAnotherActiveAdmin(User $user): void
    {
        $others = User::where('role', 'admin')->where('status', 'active')->whereKeyNot($user->id)->lockForUpdate()->count();
        if ($others === 0) {
            throw new RuleViolation(__('At least one active administrator must remain.'));
        }
    }
}
