<?php

namespace App\Contracts;

interface WordSimilarityAlgorithm
{
    public function key(string $word): string;
}
