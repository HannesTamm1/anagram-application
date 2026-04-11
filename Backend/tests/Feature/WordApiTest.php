<?php

namespace Tests\Feature;

use App\Models\Word;
use App\Services\SortedLettersAlgorithm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WordApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_words_can_be_filtered(): void
    {
        $algorithm = app(SortedLettersAlgorithm::class);

        foreach (['banana', 'apple', 'applet', 'apply'] as $word) {
            Word::query()->create([
                'word' => $word,
                'signature' => $algorithm->key($word),
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

    public function test_search_must_only_contain_letters(): void
    {
        $response = $this->getJson('/api/words?search=app%25');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['search']);
    }
}
