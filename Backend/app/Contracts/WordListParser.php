<?php

namespace App\Contracts;

interface WordListParser
{
    /**
     * @return list<string>
     */
    public function parse(string $content): array;
}
