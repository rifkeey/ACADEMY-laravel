{{-- Enquiry form shared by Contact and Corporate. Spam protection: a hidden honeypot field + rate limiting. --}}
@props(['kind' => 'general', 'programme' => '', 'programmes'])
    <form method="POST" action="{{ route('contact.store') }}" class="space-y-5" novalidate>
        @csrf
        <div class="hidden" aria-hidden="true"><label for="website">Leave this field empty</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
        <x-ui.field label="Enquiry type" for="kind">
            <x-ui.select name="kind">
                <option value="general" @selected(old('kind', $kind) === 'general')>General question</option>
                <option value="corporate" @selected(old('kind', $kind) === 'corporate')>Corporate or team training</option>
                <option value="programme_interest" @selected(old('kind', $kind) === 'programme_interest')>Register interest in a programme</option>
            </x-ui.select>
        </x-ui.field>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-ui.field label="Your name" for="name" required><x-ui.input name="name" autocomplete="name" required /></x-ui.field>
            <x-ui.field label="Email" for="email" required><x-ui.input name="email" type="email" autocomplete="email" required /></x-ui.field>
            <x-ui.field label="Organisation" for="organisation" hint="Optional"><x-ui.input name="organisation" autocomplete="organization" /></x-ui.field>
            <x-ui.field label="Phone" for="phone" hint="Optional"><x-ui.input name="phone" type="tel" autocomplete="tel" /></x-ui.field>
            <x-ui.field label="Programme" for="specialization_slug" hint="Optional">
                <x-ui.select name="specialization_slug">
                    <option value="">Not sure yet</option>
                    @foreach ($programmes as $p)<option value="{{ $p->slug }}" @selected(old('specialization_slug', $programme) === $p->slug)>{{ $p->title }}</option>@endforeach
                </x-ui.select>
            </x-ui.field>
            <x-ui.field label="Team size" for="team_size" hint="For corporate enquiries">
                <x-ui.select name="team_size">
                    <option value="">—</option>
                    @foreach (['1–5', '6–20', '21–50', '51+'] as $t)<option @selected(old('team_size') === $t)>{{ $t }}</option>@endforeach
                </x-ui.select>
            </x-ui.field>
        </div>
        <x-ui.field label="Message" for="message" required><x-ui.textarea name="message" required maxlength="4000" /></x-ui.field>
        <p class="text-xs text-subtle">We use your details only to respond to this enquiry. See our <a href="{{ route('legal', 'privacy') }}" class="underline">privacy policy</a>.</p>
        <x-ui.button>Send enquiry</x-ui.button>
    </form>
