<?php

namespace App\Domain\NewsHub\Actions;

use App\Models\Article;

final class FindExistingArticleAction
{
    public function __invoke(int $sourceId, string $externalId): ?Article
    {
        return Article::where('source_id', $sourceId)
            ->where('external_id', $externalId)
            ->first();
    }
}
