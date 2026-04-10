<?php

namespace Tests\Unit;

use App\Services\SortedLettersAlgorithm;
use App\Services\WordNormalizer;
use PHPUnit\Framework\TestCase;

class SortedLettersAlgorithmTest extends TestCase
{
    public function test_it_generates_the_same_key_for_anagrams(): void
    {
        $algorithm = new SortedLettersAlgorithm(new WordNormalizer());

        $this->assertSame($algorithm->key('stream'), $algorithm->key('master'));
    }

    public function test_it_handles_case_and_unicode_characters(): void
    {
        $algorithm = new SortedLettersAlgorithm(new WordNormalizer());

        $this->assertSame('nuõ', $algorithm->key('Õun'));
    }
}
