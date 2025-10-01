<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\NewsHub\Services\ArticleIngestService;
use App\Domain\NewsHub\Sources\SourceFetcherFactory;
use App\Domain\NewsHub\Sources\SourceNormalizerFactory;
use Carbon\Carbon;

final class NewsIngestCommand extends Command
{
    protected $signature = 'news:ingest {--source=all : all|newsdata|newsapi|guardian}';
    protected $description = 'Fetch, normalize, and ingest news articles';

    public function __construct(
        private readonly SourceFetcherFactory $sourceFetcherFactory,
        private readonly SourceNormalizerFactory $sourceNormalizerFactory
    ) {
        parent::__construct();
    }

    public function handle(ArticleIngestService $articleIngestService): int
    {
        $requestedSourceSlug = $this->option('source');
        $sourceSlugList = $requestedSourceSlug === 'all'
            ? ['newsdata','newsapi','guardian']
            : [$requestedSourceSlug];

        $totals = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($sourceSlugList as $sourceSlug) {
            // 1) Resolve fetcher + normalizer
            try {
                $sourceFetcher = $this->sourceFetcherFactory->makeFetcher($sourceSlug);
                $sourceNormalizer = $this->sourceNormalizerFactory->makeNormalizer($sourceSlug);
            } catch (\InvalidArgumentException $exception) {
                $this->warn($exception->getMessage());
                continue;
            }

            // 2) Fetch RAW iterable, normalize to DTO[]
            $rawItemsIterable = $sourceFetcher->fetchSince(Carbon::parse("-2 hours"));
            $dtoList = $sourceNormalizer->normalizeMany($rawItemsIterable);

            if (empty($dtoList)) {
                $this->warn("No items for source: {$sourceSlug}");
                continue;
            }

            // 3) Ingest
            $result = $articleIngestService->upsertMany($dtoList);

            $this->info(sprintf(
                '%s => created %d, updated %d, skipped %d',
                $sourceSlug, $result['created'], $result['updated'], $result['skipped']
            ));

            $totals['created'] += $result['created'];
            $totals['updated'] += $result['updated'];
            $totals['skipped'] += $result['skipped'];
        }

        $this->info(sprintf(
            'TOTAL => created %d, updated %d, skipped %d',
            $totals['created'], $totals['updated'], $totals['skipped']
        ));

        return self::SUCCESS;
    }
}
