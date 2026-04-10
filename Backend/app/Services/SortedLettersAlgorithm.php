<?php

namespace App\Services;

use App\Contracts\WordSimilarityAlgorithm;

class SortedLettersAlgorithm implements WordSimilarityAlgorithm
{
    public function __construct(
        protected WordNormalizer $wordNormalizer
    ) {}

    public function key(string $word): string
    {
        $chars = preg_split('//u', $this->wordNormalizer->normalize($word), -1, PREG_SPLIT_NO_EMPTY);

        sort($chars);

        return implode('', $chars);
    }
}
