<?php

namespace App\Services;

use App\Models\Word;
use Illuminate\Support\Facades\Http;

class WordImportService
{
    protected const INSERT_CHUNK_SIZE = 200;

    public function __construct(
        protected WordNormalizer $wordNormalizer
    ) {}

    public function importFromUrl(string $url): int
    {
        $response = Http::timeout(30)->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException("Failed to fetch wordbase. HTTP {$response->status()}");
        }

        $lines = preg_split('/\r\n|\r|\n/', $response->body()) ?: [];
        $timestamp = now();
        $rowsByWord = [];

        foreach ($lines as $line) {
            $word = $this->wordNormalizer->normalizeImportedWord($line);

            if ($word === null) {
                continue;
            }

            $rowsByWord[$word] = $this->makeRow($word, $timestamp);
        }

        $count = 0;

        foreach (array_chunk(array_values($rowsByWord), self::INSERT_CHUNK_SIZE) as $chunk) {
            $count += Word::query()->insertOrIgnore($chunk);
        }

        return $count;
    }

    private function makeRow(string $word, object $timestamp): array
    {
        return [
            'word' => $word,
            'signature' => $this->wordNormalizer->signature($word),
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }
}
