<?php

namespace App\Domain\NewsHub\Sources\Fetchers;

use App\Domain\NewsHub\Sources\Contracts\SourceFetcher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;


abstract class BaseHttpFetcher implements SourceFetcher
{
    public function __construct(
        protected string $baseUrl,          // full URL (already includes endpoint)
        protected array  $defaults = [],    // config default_params
        protected int    $timeout = 10,
        protected int    $maxRetries = 3,
        protected int    $retryDelayMs = 250,
        // global RL: 25 requests / 15 minutes
        protected int    $maxAttempts = 25,
        protected int    $decaySeconds = 900,
        protected int    $circuitOpenSecs = 300
    ) {}

    abstract public function slug(): string;

    /** Headers for auth, if used by the provider. */
    protected function authHeaders(): array { return []; }

    /** Query params for auth, if used by the provider. */
    protected function authQuery(): array { return []; }

    /** Provider-specific mapping: add/convert since -> query param(s). */
    abstract protected function queryForSince(?Carbon $since): array;

    /** Provider-specific: extract a simple array of raw items from JSON. */
    abstract protected function extractItems(array $json): array;

    /**
     * Provider-specific: return next page params or null to stop.
     * Example return: ['page' => $current + 1] or ['page' => $json['nextPage']]
     */
    abstract protected function nextPageParams(array $json, array $currentQuery): ?array;

    /** Optional cap to avoid runaway loops. */
    protected function maxPages(): int { return 10; }

    /** Merge defaults + auth + caller query; drop null/empty. */
    protected function buildQuery(array $extra): array
    {
        $merged = $this->defaults + $extra + $this->authQuery();
        return array_filter($merged, function ($v) {
            if ($v === null) return false;
            if (is_string($v)) return trim($v) !== '';
            if (is_array($v))  return !empty($v);
            return true;
        });
    }

    /** Safe GET with retry, RL, circuit breaker. */
    protected function safeGet(array $query): array
    {
        $slug = $this->slug();
        $key  = "fetch:$slug";

        if (RateLimiter::remaining($key, $this->maxAttempts) <= 0) {
            return Cache::get("$slug:last_success", []);
        }
        RateLimiter::hit($key, $this->decaySeconds);

        if (Cache::get("$slug:circuit_open")) {
            return Cache::get("$slug:last_success", []);
        }

        try {
            $resp = Http::withHeaders($this->authHeaders())
                ->timeout($this->timeout)
                ->retry($this->maxRetries, $this->retryDelayMs)
                ->get($this->baseUrl, $query);

            if ($resp->failed()) {
                throw new \RuntimeException("$slug http ".$resp->status());
            }

            $json = (array) $resp->json();
            Cache::put("$slug:last_success", $json, 300);
            Cache::forget("$slug:failures");
            return $json;
        } catch (Throwable $e) {
            report($e);
            $fails = Cache::increment("$slug:failures");
            if ($fails >= 5) {
                Cache::put("$slug:circuit_open", true, $this->circuitOpenSecs);
                Cache::forget("$slug:failures");
            }
            return Cache::get("$slug:last_success", []);
        }
    }

    /** Stream pages since a given time. */
    public function fetchSince(?Carbon $since): iterable
    {
        $query = $this->buildQuery($this->queryForSince($since));
        $pages = 0;

        while (true) {
            if (++$pages > $this->maxPages()) break;

            $json  = $this->safeGet($query);
            $items = $this->extractItems($json);
            if (empty($items)) break;

            foreach ($items as $item) {
                yield (array) $item; // raw provider item
            }

            $next = $this->nextPageParams($json, $query);
            if (! $next) break;
            $query = $this->buildQuery($next);
        }
    }
}
