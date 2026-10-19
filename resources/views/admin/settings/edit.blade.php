<x-layouts.admin title="Settings" description="Operational settings. Secrets such as payment keys and mail passwords are never stored here; they live in the server's .env file.">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-2xl space-y-5 rounded-2xl border border-border bg-surface p-6">
        @csrf @method('PUT')
        <x-ui.field label="Public contact email" for="contact_email" hint="Shown on the contact page. Leave empty to hide it."><x-ui.input type="email" name="contact_email" :value="$settings['contact_email'] ?? ''" /></x-ui.field>
        <x-ui.field label="Support hours" for="support_hours"><x-ui.input name="support_hours" :value="$settings['support_hours'] ?? ''" maxlength="120" /></x-ui.field>
        <x-ui.field label="Refund window (days)" for="refund_policy_days" hint="Used in the refund policy text. Leave empty until your policy is confirmed."><x-ui.input type="number" name="refund_policy_days" :value="$settings['refund_policy_days'] ?? ''" min="0" max="365" /></x-ui.field>
        <x-ui.field label="Certificate signatory" for="certificate_signatory" hint="Name and title printed on certificates, e.g. “Head of Academy”. Leave empty to print none."><x-ui.input name="certificate_signatory" :value="$settings['certificate_signatory'] ?? ''" maxlength="120" /></x-ui.field>
        <x-ui.field label="Name on the public verification page" for="verification_name_display">
            <x-ui.select name="verification_name_display">
                <option value="full" @selected(($settings['verification_name_display'] ?? 'full') === 'full')>Full name</option>
                <option value="initials" @selected(($settings['verification_name_display'] ?? '') === 'initials')>Initials only (more private)</option>
            </x-ui.select>
        </x-ui.field>
        <x-ui.button>Save settings</x-ui.button>
    </form>
</x-layouts.admin>
