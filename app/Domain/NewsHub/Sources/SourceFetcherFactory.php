<?php

namespace App\Domain\NewsHub\Sources;

use App\Domain\NewsHub\Sources\Contracts\SourceFetcher;
use InvalidArgumentException;

class SourceFetcherFactory
{
    /** @param array<string, callable():SourceFetcher> $registry */
    public function __construct(private array $registry) {}

    public function makeFetcher(string $type): SourceFetcher
    {
        if (!isset($this->registry[$type])) {
            throw new InvalidArgumentException("Unknown source type: {$type}");
        }
        return ($this->registry[$type])();
    }
}
