<?php

namespace App\Services;

class WordNormalizer
{
    public function normalize(string $value): string
    {
        return mb_strtolower(trim($value), 'UTF-8');
    }

    public function normalizeImportedWord(string $value): ?string
    {
        $word = $this->normalize($value);

        if ($word === '' || ! preg_match('/^\p{L}+$/u', $word)) {
            return null;
        }

        return $word;
    }
}
