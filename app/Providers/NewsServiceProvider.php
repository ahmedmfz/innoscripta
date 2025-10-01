<?php

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

class NewsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
         $this->app->singleton(SourceFetcherFactory::class, function () {
             return new SourceFetcherFactory([
                 'newsdata' => fn () => new NewsDataFetcher(),
                 'newsapi'  => fn () => new NewsApiFetcher(),
                 'guardian' => fn () => new GuardianFetcher(),
             ]);
         });

         $this->app->singleton(SourceNormalizerFactory::class, function () {
             return new SourceNormalizerFactory([
                 'newsdata' => fn () => new NewsDataNormalizer(),
                 'newsapi'  => fn () => new NewsApiNormalizer(),
                 'guardian' => fn () => new GuardianNormalizer(),
             ]);
         });


         $this->app->bind(ArticleRepositoryInterface::class, EloquentArticleRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
