<?php

namespace App\Domain\NewsHub\Sources\Fetchers;
use App\Domain\NewsHub\Sources\Fetchers\BaseHttpFetcher;

use Carbon\Carbon;

final class NewsApiFetcher extends BaseHttpFetcher
{
    public function __construct(
        string $baseUrl,
        private readonly string $apiKey,
        array $defaults = []
    ) {
        parent::__construct($baseUrl, $defaults);
    }

    public function slug(): string
    {
        return 'newsapi';
    }

    /** Auth via header for NewsAPI */
    protected function authQuery(): array
    {
        return ['apiKey' => $this->apiKey];
    }

    /** Map Carbon $since → NewsAPI `from` (ISO 8601) + paging defaults */
    protected function queryForSince(?Carbon $since): array
    {
        return array_filter([
            'from'     => $since?->toIso8601String(),
            'page'     => 1,
//             'pageSize' => $this->defaults['pageSize'] ?? 10,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /** Pull article list */
    protected function extractItems(array $json): array
    {
        return (array) ($json['articles'] ?? []);
    }

    /** Advance page while we still get items (fallback if totalResults missing) */
    protected function nextPageParams(array $json, array $currentQuery): ?array
    {
        $currentPage = (int) ($currentQuery['page'] ?? 1);
        $nextPage    = $currentPage + 1;

        // if totalResults exists, stop when we’ve covered it
        $pageSize     = (int) ($currentQuery['pageSize'] ?? 10);
        $totalResults = isset($json['totalResults']) ? (int) $json['totalResults'] : null;

        if ($totalResults !== null && $currentPage * $pageSize >= $totalResults) {
            return null;
        }

        return array_replace($currentQuery, ['page' => $nextPage]);
    }
}
