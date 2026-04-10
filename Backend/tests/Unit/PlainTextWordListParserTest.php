<?php

namespace Tests\Unit;

use App\Services\PlainTextWordListParser;
use App\Services\WordNormalizer;
use PHPUnit\Framework\TestCase;

class PlainTextWordListParserTest extends TestCase
{
    public function test_it_returns_unique_normalized_words_from_plain_text(): void
    {
        $parser = new PlainTextWordListParser(new WordNormalizer());

        $this->assertSame(
            ['stream', 'master', 'õun'],
            $parser->parse(" Stream \nmaster\nStream\nfoo-bar\nõun\n123\n")
        );
    }
}
