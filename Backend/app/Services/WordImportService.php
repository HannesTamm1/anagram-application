<?php

namespace App\Services;

use App\Contracts\WordListParser;
use App\Contracts\WordSimilarityAlgorithm;
use App\Models\Word;
use Illuminate\Support\Facades\Http;

class WordImportService
{
    protected const INSERT_CHUNK_SIZE = 200;

    public function __construct(
        protected WordListParser $wordListParser,
        protected WordSimilarityAlgorithm $wordSimilarityAlgorithm
    ) {}

    public function importFromUrl(string $url): int
    {
        $response = Http::timeout(30)->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException("Failed to fetch wordbase. HTTP {$response->status()}");
        }

        $timestamp = now();
        $rowsByWord = [];

        foreach ($this->wordListParser->parse($response->body()) as $word) {
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
            'signature' => $this->wordSimilarityAlgorithm->key($word),
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }
}
