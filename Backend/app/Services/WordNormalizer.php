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

    public function signature(string $word): string
    {
        $chars = preg_split('//u', $this->normalize($word), -1, PREG_SPLIT_NO_EMPTY);

        sort($chars);

        return implode('', $chars);
    }
}
