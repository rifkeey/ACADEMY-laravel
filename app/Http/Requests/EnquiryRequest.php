<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Contact, corporate and register-interest form. */
class EnquiryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'kind' => ['required', 'in:general,corporate,programme_interest'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'organisation' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'team_size' => ['nullable', 'string', 'max:40'],
            'specialization_slug' => ['nullable', 'string', 'max:80'],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
            'website' => ['nullable', 'max:0'],   // honeypot: real people leave this empty
        ];
    }

    public function messages(): array
    {
        return ['message.min' => 'Tell us a little more (at least 10 characters).'];
    }
}
