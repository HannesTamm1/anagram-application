<?php

namespace Tests\Unit;

use App\Services\WordNormalizer;
use PHPUnit\Framework\TestCase;

class WordNormalizerTest extends TestCase
{
    public function test_normalize_trims_and_lowercases_the_word(): void
    {
        $normalizer = new WordNormalizer();

        $this->assertSame('stream', $normalizer->normalize(' Stream '));
    }

    public function test_signature_is_the_same_for_anagrams(): void
    {
        $normalizer = new WordNormalizer();

        $this->assertSame(
            $normalizer->signature('stream'),
            $normalizer->signature('master')
        );
    }

    public function test_signature_ignores_case_and_spaces(): void
    {
        $normalizer = new WordNormalizer();

        $this->assertSame('aemrst', $normalizer->signature(' Stream '));
    }

    public function test_imported_word_allows_unicode_letters(): void
    {
        $normalizer = new WordNormalizer();

        $this->assertSame('õun', $normalizer->normalizeImportedWord(' ÕUN '));
    }

    public function test_imported_word_rejects_numbers_and_symbols(): void
    {
        $normalizer = new WordNormalizer();

        $this->assertNull($normalizer->normalizeImportedWord('foo-bar'));
        $this->assertNull($normalizer->normalizeImportedWord('123'));
        $this->assertNull($normalizer->normalizeImportedWord(''));
    }
}
