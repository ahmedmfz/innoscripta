<?php

namespace App\Domain\NewsHub\Sources\Fetchers;
use App\Domain\NewsHub\Sources\Fetchers\BaseHttpFetcher;

use Carbon\Carbon;

final class GuardianFetcher extends BaseHttpFetcher
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
        return 'guardian';
    }

    /** Auth is a query param for Guardian */
    protected function authQuery(): array
    {
        return ['api-key' => $this->apiKey];
    }

    /** Map Carbon $since → Guardian `from-date` (YYYY-MM-DD) + paging defaults */
    protected function queryForSince(?Carbon $since): array
    {
        return array_filter([
            'from-date' => $since?->toDateString(),
            'page'      => 1,
//             'page-size' => $this->defaults['page_size'] ?? 10,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /** Pull results array */
    protected function extractItems(array $json): array
    {
        return (array) ($json['response']['results'] ?? []);
    }

    /** Use Guardian’s response paging metadata */
    protected function nextPageParams(array $json, array $currentQuery): ?array
    {
        $response     = (array) ($json['response'] ?? []);
        $currentPage  = (int) ($response['currentPage'] ?? ($currentQuery['page'] ?? 1));
        $totalPages   = (int) ($response['pages'] ?? 1);

        if ($currentPage >= $totalPages) {
            return null;
        }

        return array_replace($currentQuery, ['page' => $currentPage + 1]);
    }
}
