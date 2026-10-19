<x-layouts.admin title="Users" description="Change roles and suspend or reactivate accounts. You cannot change your own account, and at least one active administrator always remains.">
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
        <div class="w-full sm:w-72"><x-ui.field label="Search name or email" for="q"><x-ui.input name="q" :value="$q" type="search" /></x-ui.field></div>
        <div class="w-40"><x-ui.field label="Role" for="role">
            <x-ui.select name="role"><option value="">All roles</option>@foreach (\App\Support\Permissions::ROLES as $r)<option value="{{ $r }}" @selected($role === $r)>{{ ucfirst($r) }}</option>@endforeach</x-ui.select>
        </x-ui.field></div>
        <x-ui.button variant="secondary">Filter</x-ui.button>
    </form>

    <x-ui.table :head="['User', 'Role', 'Status', 'Enrolments', 'Joined', '']" caption="User accounts">
        @foreach ($users as $u)
            <tr>
                <x-ui.td><p class="font-medium">{{ $u->name }}</p><p class="text-xs text-muted">{{ $u->email }}@if (! $u->email_verified_at) · <span class="text-warning">unverified</span>@endif</p></x-ui.td>
                <x-ui.td>
                    @if ($u->is(auth()->user()))
                        <x-ui.pill tone="accent">{{ ucfirst($u->role) }} (you)</x-ui.pill>
                    @else
                        <form method="POST" action="{{ route('admin.users.role', $u) }}" class="flex items-center gap-2">
                            @csrf @method('PUT')
                            <label for="role-{{ $u->id }}" class="sr-only">Role for {{ $u->email }}</label>
                            <select id="role-{{ $u->id }}" name="role" class="h-8 rounded-md border border-border-strong bg-bg px-2 text-sm">
                                @foreach (\App\Support\Permissions::ROLES as $r)<option value="{{ $r }}" @selected($u->role === $r)>{{ ucfirst($r) }}</option>@endforeach
                            </select>
                            <x-ui.button variant="ghost" size="sm">Save</x-ui.button>
                        </form>
                    @endif
                </x-ui.td>
                <x-ui.td>@include('admin.partials.status', ['value' => $u->status])</x-ui.td>
                <x-ui.td class="tabular-nums">{{ $u->enrolments_count }}</x-ui.td>
                <x-ui.td class="whitespace-nowrap text-muted">{{ $u->created_at->format('j M Y') }}</x-ui.td>
                <x-ui.td>
                    @unless ($u->is(auth()->user()))
                        @if ($u->status === 'active')
                            <x-ui.confirm :action="route('admin.users.status', $u)" method="PUT" label="Suspend" title="Suspend {{ $u->email }}?" description="They are signed out everywhere and cannot sign in until reactivated." confirm-label="Suspend account" :require-reason="true">
                                <input type="hidden" name="status" value="suspended">
                            </x-ui.confirm>
                        @else
                            <form method="POST" action="{{ route('admin.users.status', $u) }}">@csrf @method('PUT')<input type="hidden" name="status" value="active"><x-ui.button variant="secondary" size="sm">Reactivate</x-ui.button></form>
                        @endif
                    @endunless
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>
    <x-admin.pager :items="$users" />
</x-layouts.admin>
