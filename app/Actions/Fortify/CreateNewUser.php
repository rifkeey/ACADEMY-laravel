<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/**
 * Registration. New accounts are always learners: the role column is not mass-assignable and
 * is never read from the request.
 */
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:254', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
            'terms' => ['accepted'],
        ], [
            'name.required' => __('Enter your full name as it should appear on certificates.'),
            'terms.accepted' => __('You must accept the terms and the acceptable-use policy.'),
        ])->validate();

        $user = User::create([
            'name' => trim($input['name']),
            'email' => mb_strtolower(trim($input['email'])),
            'password' => $input['password'],   // hashed by the model's "hashed" cast
        ]);
        // Keep the language the visitor was using, so emails (verification first) arrive in it.
        $user->forceFill(['locale' => app()->getLocale()])->save();

        return $user;
    }
}
