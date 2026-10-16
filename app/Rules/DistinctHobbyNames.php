<?php

namespace App\Rules;

use App\Models\Hobby;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DistinctHobbyNames implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            return;
        }

        $normalizedNames = array_map(
            fn (mixed $name): string => mb_strtolower(Hobby::normalizeName((string) $name)),
            $value,
        );

        if (count($normalizedNames) !== count(array_unique($normalizedNames))) {
            $fail('Hobby names must be unique for the same user.');
        }
    }
}
