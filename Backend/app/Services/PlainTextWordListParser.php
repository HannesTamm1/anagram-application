<?php

namespace App\Services;

use App\Contracts\WordListParser;

class PlainTextWordListParser implements WordListParser
{
    public function __construct(
        protected WordNormalizer $wordNormalizer
    ) {}

    public function parse(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        $words = [];

        foreach ($lines as $line) {
            $word = $this->wordNormalizer->normalizeImportedWord($line);

            if ($word === null) {
                continue;
            }

            $words[$word] = $word;
        }

        return array_values($words);
    }
}
