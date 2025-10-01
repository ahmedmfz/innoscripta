<?php

namespace App\Domain\NewsHub\Actions;

use App\Models\Article;
use App\Models\Category;

final class SyncArticleCategoriesAction
{
    /**
     * @param string[] $categorySlugs
     */
    public function __invoke(Article $articleModel, array $categorySlugs): void
    {
        if (empty($categorySlugs)) {
            return;
        }

        $categoryIds = [];
        foreach ($categorySlugs as $categorySlug) {
            $normalizedSlug = strtolower(trim($categorySlug));
            if ($normalizedSlug === '') {
                continue;
            }
            $categoryIds[] = Category::firstOrCreate(
                ['slug' => $normalizedSlug],
                ['label' => ucfirst($normalizedSlug)]
            )->id;
        }

        if ($categoryIds) {
            $articleModel->categories()->syncWithoutDetaching($categoryIds);
        }
    }
}
