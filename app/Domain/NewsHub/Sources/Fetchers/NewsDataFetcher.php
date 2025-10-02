<?php

namespace App\Domain\NewsHub\Sources\Fetchers;
use App\Domain\NewsHub\Sources\Fetchers\BaseHttpFetcher;
use Carbon\Carbon;

final class NewsDataFetcher extends BaseHttpFetcher
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
        return 'newsdata';
    }

    /** Auth is a query param for NewsData */
    protected function authQuery(): array
    {
        return ['apiKey' => $this->apiKey];
    }

    /** Map Carbon $since → provider’s `from_date` (many plans support it) */
    protected function queryForSince(?Carbon $since): array
    {
        return array_filter([
//             'from_date' => $since?->toDateString(),
//             'pageSize'  => $this->defaults['pageSize'] ?? 10,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /** Pull results array */
    protected function extractItems(array $json): array
    {
        return (array) ($json['results'] ?? []);
    }

    protected function nextPageParams(array $json, array $currentQuery): ?array
    {
        $nextToken = $json['nextPage'] ?? null;
        if (is_string($nextToken) && trim($nextToken) !== '') {
            // Keep existing filters; just add the token as `page`
            $updatedQuery = $currentQuery;
            $updatedQuery['page'] = $nextToken;
            return $updatedQuery;
        }

        // No token → no more pages
        return null;
    }

}
