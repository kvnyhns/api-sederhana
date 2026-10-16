<?php

namespace App\Http\Requests;

use App\Models\Hobby;
use App\Rules\DistinctHobbyNames;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_array($this->input('hobbies'))) {
            $hobbies = array_map(
                static fn (mixed $hobby): mixed => is_string($hobby)
                    ? Hobby::normalizeName($hobby)
                    : $hobby,
                $this->input('hobbies'),
            );

            $this->merge([
                'hobbies' => array_values(array_filter(
                    $hobbies,
                    static fn (mixed $hobby): bool => $hobby !== null && $hobby !== '',
                )),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'hobbies' => ['nullable', 'array', new DistinctHobbyNames],
            'hobbies.*' => ['required', 'string', 'max:100'],
        ];
    }
}
