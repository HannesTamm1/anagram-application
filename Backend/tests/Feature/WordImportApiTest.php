<?php

namespace Tests\Feature;

use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WordImportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_words_can_be_imported(): void
    {
        Http::fake([
            'https://example.com/words.txt' => Http::response(" Stream \nmaster\nStream\nfoo-bar\nõun\n123\n", 200),
        ]);

        $response = $this->postJson('/api/words/import', [
            'url' => 'https://example.com/words.txt',
        ]);

        $signature = app(\App\Services\WordNormalizer::class)->signature('stream');

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Wordbase imported successfully.',
                'inserted' => 3,
            ]);

        $this->assertDatabaseCount('words', 3);
        $this->assertDatabaseHas('words', ['word' => 'stream', 'signature' => $signature]);
        $this->assertDatabaseHas('words', ['word' => 'master']);
        $this->assertDatabaseHas('words', ['word' => 'õun']);
    }

    public function test_import_returns_an_error_when_the_url_fails(): void
    {
        Word::query()->create([
            'word' => 'stream',
            'signature' => app(\App\Services\WordNormalizer::class)->signature('stream'),
        ]);

        Http::fake([
            'https://example.com/words.txt' => Http::response('Unavailable', 500),
        ]);

        $response = $this->postJson('/api/words/import', [
            'url' => 'https://example.com/words.txt',
        ]);

        $response
            ->assertStatus(502)
            ->assertExactJson([
                'message' => 'Failed to fetch wordbase. HTTP 500',
            ]);

        $this->assertDatabaseCount('words', 1);
    }
}
