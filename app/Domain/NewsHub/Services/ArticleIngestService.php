<?php

namespace App\Domain\NewsHub\Services;

use Illuminate\Support\Facades\DB;
use App\Domain\NewsHub\DTO\RemoteArticleDTO;
use App\Domain\NewsHub\Actions\ResolveSourceBySlugAction;
use App\Domain\NewsHub\Actions\ComputeCanonicalHashAction;
use App\Domain\NewsHub\Actions\IsDuplicateByHashAction;
use App\Domain\NewsHub\Actions\FindExistingArticleAction;
use App\Domain\NewsHub\Actions\BuildArticlePayloadAction;
use App\Domain\NewsHub\Actions\UpsertArticleAction;
use App\Domain\NewsHub\Actions\SyncArticleAuthorsAction;
use App\Domain\NewsHub\Actions\SyncArticleCategoriesAction;

final class ArticleIngestService
{
    public function __construct(
        private readonly ResolveSourceBySlugAction     $resolveSourceBySlugAction,
        private readonly ComputeCanonicalHashAction    $computeCanonicalHashAction,
        private readonly IsDuplicateByHashAction       $isDuplicateByHashAction,
        private readonly FindExistingArticleAction     $findExistingArticleAction,
        private readonly BuildArticlePayloadAction     $buildArticlePayloadAction,
        private readonly UpsertArticleAction           $upsertArticleAction,
        private readonly SyncArticleAuthorsAction      $syncArticleAuthorsAction,
        private readonly SyncArticleCategoriesAction   $syncArticleCategoriesAction,
    ) {}

    /**
     * @param RemoteArticleDTO[] $normalizedArticles
     * @return array{created:int, updated:int, skipped:int}
     */
    public function upsertMany(array $normalizedArticles): array
    {
        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use ($normalizedArticles, &$createdCount, &$updatedCount, &$skippedCount) {
            foreach ($normalizedArticles as $normalizedArticle) {
                // 1) Resolve source
                $sourceModel = ($this->resolveSourceBySlugAction)($normalizedArticle->source_slug);

                // 2) Compute canonical hash and check duplicates (cross-source)
                $canonicalUrlHash = ($this->computeCanonicalHashAction)($normalizedArticle->url);
                if (($this->isDuplicateByHashAction)($canonicalUrlHash)) {
                    $skippedCount++;
                    continue;
                }

                // 3) Check existing article (per-source unique)
                $existingArticle = ($this->findExistingArticleAction)($sourceModel->id, $normalizedArticle->external_id);

                // 4) Build DB payload
                $databasePayload = ($this->buildArticlePayloadAction)($normalizedArticle, $sourceModel->id, $canonicalUrlHash);

                // 5) Create or update
                $upsertResult = ($this->upsertArticleAction)($existingArticle, $databasePayload);
                $articleModel = $upsertResult['model'];
                $upsertResult['was_created'] ? $createdCount++ : $updatedCount++;

                // 6) Sync authors & categories (idempotent)
                ($this->syncArticleAuthorsAction)($articleModel, $normalizedArticle->authors);
                ($this->syncArticleCategoriesAction)($articleModel, $normalizedArticle->categories);
            }
        });

        return ['created' => $createdCount, 'updated' => $updatedCount, 'skipped' => $skippedCount];
    }
}
