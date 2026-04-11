<?php

namespace App\Http\Requests;

use App\Services\SafeRemoteUrl;
use Illuminate\Foundation\Http\FormRequest;

class WordImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => [
                'required',
                'string',
                'max:2048',
                'url:http,https',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value)) {
                        return;
                    }

                    if (! app(SafeRemoteUrl::class)->isSafe($value)) {
                        $fail('The URL must target a public HTTP or HTTPS host.');
                    }
                },
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'url' => is_string($this->input('url')) ? trim($this->input('url')) : $this->input('url'),
        ]);
    }
}
