<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Domain\NewsHub\Services\ArticleIngestService;
use App\Domain\NewsHub\Sources\SourceFetcherFactory;
use App\Domain\NewsHub\Sources\SourceNormalizerFactory;


class NewsIngestCommand extends Command
{
    protected $signature = 'news:ingest
        {--source=all : all|newsdata|newsapi|guardian}
        {--since= : ISO8601 or relative time (e.g., "-24 hours")}';

    protected $description = 'Fetch, normalize, and ingest news articles';

    public function __construct(
        private readonly SourceFetcherFactory $sourceFetcherFactory,
        private readonly SourceNormalizerFactory $sourceNormalizerFactory
    ) {
        parent::__construct();
    }

    public function handle(ArticleIngestService $articleIngestService): int
    {
        // prevent overlap if accidentally run while scheduler is active
        $lock = Cache::lock('news:ingest:lock', 14 * 60); // 14 minutes
        if (! $lock->get()) {
            $this->warn('Ingest is already running. Skipping this invocation.');
            return self::SUCCESS;
        }

        try {
            $requestedSourceSlug = (string) $this->option('source');
            $sinceOption         = $this->option('since');
            $since               = $sinceOption ? Carbon::parse($sinceOption) : Carbon::parse('-24 hours');

            $sourceSlugList = $requestedSourceSlug === 'all'
                ? ['newsdata', 'newsapi', 'guardian']
                : [$requestedSourceSlug];

            $totals = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

            foreach ($sourceSlugList as $sourceSlug) {
                // 1) Resolve fetcher + normalizer
                try {
                    $sourceFetcher    = $this->sourceFetcherFactory->makeFetcher($sourceSlug);
                    $sourceNormalizer = $this->sourceNormalizerFactory->makeNormalizer($sourceSlug);
                } catch (\InvalidArgumentException $exception) {
                    $this->warn($exception->getMessage());
                    continue;
                }

                $this->info("Fetching {$sourceSlug} since {$since->toIso8601String()} ...");

                // 2) Fetch RAW iterable (stream), normalize to DTO[]
                $rawItemsIterable = $sourceFetcher->fetchSince($since);
                $normalizedDtos   = $sourceNormalizer->normalizeMany($rawItemsIterable);

                if (empty($normalizedDtos)) {
                    $this->warn("No items for source: {$sourceSlug}");
                    continue;
                }

                // 3) Ingest (service handles per-item resilience)
                $result = $articleIngestService->upsertMany($normalizedDtos);

                $created = (int) ($result['created'] ?? 0);
                $updated = (int) ($result['updated'] ?? 0);
                $skipped = (int) ($result['skipped'] ?? 0);
                $failed  = (int) ($result['failed']  ?? 0);

                $this->info(sprintf(
                    '%s => created %d, updated %d, skipped %d%s',
                    $sourceSlug,
                    $created,
                    $updated,
                    $skipped,
                    $failed ? ", failed {$failed}" : ''
                ));

                $totals['created'] += $created;
                $totals['updated'] += $updated;
                $totals['skipped'] += $skipped;
                $totals['failed']  += $failed;
            }

            $this->info(sprintf(
                'TOTAL => created %d, updated %d, skipped %d%s',
                $totals['created'], $totals['updated'], $totals['skipped'],
                $totals['failed'] ? ", failed {$totals['failed']}" : ''
            ));

            return self::SUCCESS;
        } finally {
            $lock->release();
        }
    }
}
