<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnagramLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'word' => ['required', 'string', 'max:64', 'regex:/^\p{L}+$/u'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'word' => $this->normalizeLettersOnlyInput($this->input('word')),
        ]);
    }

    private function normalizeLettersOnlyInput(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return mb_strtolower(trim($value), 'UTF-8');
    }
}
