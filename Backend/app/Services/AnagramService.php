<?php

namespace App\Services;

use App\Models\Word;
use Illuminate\Support\Collection;

class AnagramService
{
    public function __construct(
        protected WordNormalizer $wordNormalizer
    ) {}

    public function findAnagrams(string $input): Collection
    {
        $normalized = $this->wordNormalizer->normalize($input);
        $signature = $this->wordNormalizer->signature($normalized);

        return Word::query()
            ->where('signature', $signature)
            ->where('word', '!=', $normalized)
            ->orderBy('word')
            ->pluck('word');
    }
}
