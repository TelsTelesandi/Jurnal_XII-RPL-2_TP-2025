<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Waad\ProfanityFilter\Facades\ProfanityFilter;  // Import facade package

class NoProfanity implements ValidationRule
{
    /**
     * Run the validation rule.
     */
   public function validate(string $attribute, mixed $value, Closure $fail): void
{
}
}