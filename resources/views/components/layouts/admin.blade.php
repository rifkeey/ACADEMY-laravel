{{--
    Admin area layout. Navigation only shows sections the signed-in staff member may use; each
    controller action still checks its own permission, so hiding a link is never the protection.
--}}
@props(['title', 'description' => null])
@php
    $groups = [
        'Overview' => [['admin.dashboard', 'Dashboard', 'gauge', 'admin.access', 'admin.dashboard']],
        'Content' => [
            ['admin.programmes.index', 'Programmes', 'layers', 'content.edit', 'admin.programmes.*|admin.lessons.*'],
            ['admin.labs.index', 'Labs', 'flask-conical', 'content.edit', 'admin.labs.*'],
            ['admin.questions.index', 'Question bank', 'file-question', 'questions.edit', 'admin.questions.*'],
            ['admin.exams.index', 'Examinations', 'file-check-2', 'exams.configure', 'admin.exams.*'],
            ['admin.sources.index', 'Sources', 'library', 'content.edit', 'admin.sources.*'],
            ['admin.articles.index', 'Articles', 'book-open', 'content.edit', 'admin.articles.*'],
        ],
        'Learners and sales' => [
            ['admin.users.index', 'Users', 'users', 'users.manage', 'admin.users.*'],
            ['admin.orders.index', 'Orders and enrolments', 'receipt', 'commerce.manage|enrolments.manage', 'admin.orders.*'],
            ['admin.pricing.index', 'Pricing and discounts', 'tag', 'commerce.manage', 'admin.pricing.*'],
            ['admin.certificates.index', 'Certificates', 'award', 'credentials.manage', 'admin.certificates.*'],
            ['admin.badges.index', 'Badges', 'badge-check', 'credentials.manage', 'admin.badges.*'],
            ['admin.enquiries.index', 'Enquiries', 'inbox', 'enquiries.manage', 'admin.enquiries.*'],
        ],
        'System' => [
            ['admin.settings.edit', 'Settings', 'settings', 'settings.manage', 'admin.settings.*'],
            ['admin.audit.index', 'Audit log', 'scroll-text', 'audit.read', 'admin.audit.*'],
        ],
    ];
    $user = auth()->user();
    $can = fn (string $perms) => collect(explode('|', $perms))->contains(fn ($p) => $user->can($p));
@endphp
<x-layouts.base :title="$title.' · Admin'" :noindex="true">
    <div class="min-h-dvh lg:grid lg:grid-cols-[15rem_1fr]">
        <aside class="border-b border-border bg-surface lg:sticky lg:top-0 lg:h-dvh lg:overflow-y-auto lg:border-b-0 lg:border-r">
            <div class="flex h-16 items-center justify-between gap-3 px-4">
                <a href="{{ route('admin.dashboard') }}" class="text-text" aria-label="Admin dashboard"><x-brand.lockup division="Academy" size="sm" /></a>
                <x-ui.pill tone="accent" class="lg:hidden">Admin</x-ui.pill>
            </div>
            {{-- On phones the navigation collapses into a native <details> element. --}}
            <details class="group px-3 pb-3 lg:hidden">
                <summary class="flex cursor-pointer list-none items-center justify-between rounded-lg border border-border px-3 py-2 text-sm [&::-webkit-details-marker]:hidden">Admin menu <x-icon name="chevron-down" class="size-4 transition group-open:rotate-180" /></summary>
                <div class="mt-2">@include('admin.partials.nav', ['groups' => $groups, 'can' => $can])</div>
            </details>
            <div class="hidden px-3 pb-6 lg:block">@include('admin.partials.nav', ['groups' => $groups, 'can' => $can])</div>
        </aside>

        <div class="min-w-0">
            <header class="flex h-16 items-center justify-between gap-3 border-b border-border px-4 sm:px-8">
                <p class="truncate text-sm text-muted">Signed in as <span class="text-text">{{ $user->name }}</span> · {{ ucfirst($user->role) }}</p>
                <div class="flex items-center gap-2">
                    <x-ui.button :href="route('home')" variant="ghost" size="sm">View site</x-ui.button>
                    <form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button variant="secondary" size="sm">Sign out</x-ui.button></form>
                </div>
            </header>
            <main id="main" class="px-4 py-8 sm:px-8">
                <div class="mb-8">
                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ $title }}</h1>
                    @if ($description)<p class="mt-2 max-w-3xl text-sm text-muted">{{ $description }}</p>@endif
                </div>
                @foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $tone)
                    @if (session($key))<x-ui.alert :tone="$tone" class="mb-6">{{ session($key) }}</x-ui.alert>@endif
                @endforeach
                @if ($errors->any())
                    <x-ui.alert tone="danger" title="Please correct the highlighted fields" class="mb-6">
                        <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </x-ui.alert>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.base>
