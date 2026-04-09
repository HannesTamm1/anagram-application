<?php

namespace App\Services;

use App\Models\Word;
use Illuminate\Support\Collection;

class AnagramService
{
    public function signature(string $word): string
    {
        $letters = mb_strtolower($word);
        $chars = preg_split('//u', $letters, -1, PREG_SPLIT_NO_EMPTY);

        sort($chars);

        return implode('', $chars);
    }

    public function findAnagrams(string $input): Collection
    {
        $normalized = mb_strtolower(trim($input));
        $signature = $this->signature($normalized);

        return Word::query()
            ->where('signature', $signature)
            ->where('word', '!=', $normalized)
            ->orderBy('word')
            ->pluck('word');
    }
}
