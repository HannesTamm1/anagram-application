<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WordIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:64', 'regex:/^\p{L}+$/u'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalizedSearch = $this->normalizeLettersOnlyInput($this->input('search'));

        $this->merge([
            'search' => $normalizedSearch === '' ? null : $normalizedSearch,
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
