<?php

namespace App\Domain\NewsHub\Sources\Contracts;

use App\Domain\NewsHub\DTOs\RemoteArticleDTO;

interface SourceNormalizer
{
    public function normalizeItem(array $rawItem): RemoteArticleDTO;
    public function normalizeMany(iterable $rawItems): array;
}
