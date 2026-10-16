<?php

namespace App\Http\Requests;

use App\Rules\DistinctHobbyNames;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends StoreUserRequest
{
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'hobbies' => ['nullable', 'array', new DistinctHobbyNames],
            'hobbies.*' => ['required', 'string', 'max:100'],
        ];
    }
}
