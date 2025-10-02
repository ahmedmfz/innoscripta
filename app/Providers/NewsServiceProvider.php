<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Domain\NewsHub\Sources\SourceFetcherFactory;
use App\Domain\NewsHub\Sources\SourceNormalizerFactory;

use App\Domain\NewsHub\Sources\Fetchers\NewsDataFetcher;
use App\Domain\NewsHub\Sources\Fetchers\NewsApiFetcher;
use App\Domain\NewsHub\Sources\Fetchers\GuardianFetcher;

use App\Domain\NewsHub\Normalizers\NewsDataNormalizer;
use App\Domain\NewsHub\Normalizers\NewsApiNormalizer;
use App\Domain\NewsHub\Normalizers\GuardianNormalizer;

use App\Domain\NewsHub\Repositories\ArticleRepositoryInterface;
use App\Domain\NewsHub\Repositories\EloquentArticleRepository;


final class NewsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SourceNormalizerFactory::class, function () {
            return new SourceNormalizerFactory([
                'newsdata' => fn () => new NewsDataNormalizer(),
                'newsapi'  => fn () => new NewsApiNormalizer(),
                'guardian' => fn () => new GuardianNormalizer(),
            ]);
        });

        $this->app->singleton(SourceFetcherFactory::class, function () {
            $sources = (array) config('newshub.sources', []);

            return new SourceFetcherFactory([
                'newsdata' => function () use ($sources) {
                    $def = $sources['newsdata'] ?? [];
                    return new NewsDataFetcher(
                        baseUrl:  (string) ($def['base_url'] ?? ''),
                        apiKey:   (string) ($def['api_key'] ?? ''),
                        defaults: (array)  ($def['default_params'] ?? [])
                    );
                },
                'newsapi' => function () use ($sources) {
                    $def = $sources['newsapi'] ?? [];
                    return new NewsApiFetcher(
                        baseUrl:  (string) ($def['base_url'] ?? ''),
                        apiKey:   (string) ($def['api_key'] ?? ''),
                        defaults: (array)  ($def['default_params'] ?? [])
                    );
                },
                'guardian' => function () use ($sources) {
                    $def = $sources['guardian'] ?? [];
                    return new GuardianFetcher(
                        baseUrl:  (string) ($def['base_url'] ?? ''),
                        apiKey:   (string) ($def['api_key'] ?? ''),
                        defaults: (array)  ($def['default_params'] ?? [])
                    );
                },
            ]);
        });

        $this->app->bind(ArticleRepositoryInterface::class, EloquentArticleRepository::class);
    }

    public function boot(): void {}
}
