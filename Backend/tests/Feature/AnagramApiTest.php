<?php

namespace Tests\Feature;

use App\Models\Word;
use App\Services\SortedLettersAlgorithm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnagramApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finds_anagrams_for_a_word(): void
    {
        $algorithm = app(SortedLettersAlgorithm::class);

        foreach (['stream', 'tamers', 'maters', 'master', 'banana'] as $word) {
            Word::query()->create([
                'word' => $word,
                'signature' => $algorithm->key($word),
            ]);
        }

        $response = $this->getJson('/api/anagrams?word=stream');

        $response
            ->assertOk()
            ->assertJson([
                'word' => 'stream',
                'anagrams' => ['master', 'maters', 'tamers'],
                'count' => 3,
            ]);
    }

    public function test_word_is_required(): void
    {
        $response = $this->getJson('/api/anagrams');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['word']);
    }
}
