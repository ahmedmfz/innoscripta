<?php

namespace App\Domain\NewsHub\Sources;

use App\Domain\NewsHub\Sources\Contracts\SourceNormalizer;
use InvalidArgumentException;

class SourceNormalizerFactory
{
    /**
     * @param array<string, callable(): SourceNormalizer> $normalizerRegistry
     */
    public function __construct(private array $normalizerRegistry) {}

    public function makeNormalizer(string $sourceSlug): SourceNormalizer
    {
        if (!isset($this->normalizerRegistry[$sourceSlug])) {
            throw new InvalidArgumentException("Unknown source slug: {$sourceSlug}");
        }
        return ($this->normalizerRegistry[$sourceSlug])();
    }
}
