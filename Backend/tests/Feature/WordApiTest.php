<?php

namespace Tests\Feature;

use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WordApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_words_can_be_filtered(): void
    {
        $normalizer = app(\App\Services\WordNormalizer::class);

        foreach (['banana', 'apple', 'applet', 'apply'] as $word) {
            Word::query()->create([
                'word' => $word,
                'signature' => $normalizer->signature($word),
            ]);
        }

        $response = $this->getJson('/api/words?search=%20app%20&per_page=2');

        $response
            ->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonPath('per_page', 2)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.word', 'apple')
            ->assertJsonPath('data.1.word', 'applet');
    }

    public function test_per_page_must_be_valid(): void
    {
        $response = $this->getJson('/api/words?per_page=101');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }
}
