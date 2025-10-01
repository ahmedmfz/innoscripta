<?php

namespace App\Domain\NewsHub\Actions;

use App\Models\Article;
use App\Models\Author;

final class SyncArticleAuthorsAction
{
    /**
     * @param string[] $authorNames
     */
    public function __invoke(Article $articleModel, array $authorNames): void
    {
        if (empty($authorNames)) {
            return;
        }

        $authorIds = [];
        foreach ($authorNames as $authorName) {
            $trimmedAuthorName = trim($authorName);
            if ($trimmedAuthorName === '') {
                continue;
            }
            $authorIds[] = Author::firstOrCreate(['name' => $trimmedAuthorName])->id;
        }

        if ($authorIds) {
            $articleModel->authors()->syncWithoutDetaching($authorIds);
        }
    }
}
