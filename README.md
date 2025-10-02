# News Hub

Ingest and normalize news from multiple providers (NewsAPI, The Guardian, NewsData.io), then upsert into a unified schema for use by your app.

## Table of contents
- [Features](#features)
- [Architecture](#architecture)
- [Requirements](#requirements)
- [Quick start](#quick-start)
- [Configuration](#configuration)
- [Running](#running)
- [Scheduling (Laravel 11+)](#scheduling-laravel-11)
- [Data flow](#data-flow)
- [Extending: add a new source](#extending-add-a-new-source)
- [Resilience & limits](#resilience--limits)

---

## Features
- Streamed fetching (`fetchSince()`), low memory, paginated per provider
- Per-source **normalizers** → consistent `RemoteArticleDTO`
- Idempotent ingest with canonical URL hash, de-dup, and upsert
- Authors/categories sync (idempotent)
- Rate limits: **25 requests / 15 minutes / source**
- Circuit breaker + cached last-success fallback
- Safe sanitization to avoid DB truncation crashes (language codes, string clamps)
- Scheduled ingestion every 15 minutes (Asia/Dubai)

---

## Architecture

```
app/Domain/NewsHub
├─ Actions/               # small single-responsibility domain actions
├─ DTOs/          # RemoteArticleDTO, etc.
├─ Normalizers/           # Source → RemoteArticleDTO
├─ Repositories/          # ArticleRepositoryInterface + Eloquent impl
├─ Services/              # ArticleIngestService (normalize → upsert)
└─ Sources/
   ├─ Contracts/          # SourceFetcher, SourceNormalizer
   ├─ Fetchers/           # BaseHttpFetcher + {NewsApi,Guardian,NewsData}Fetcher
   ├─ SourceFetcherFactory.php
   └─ SourceNormalizerFactory.php
```

- **Fetchers** talk to external APIs and yield **raw** provider items.
- **Normalizers** map raw items → `RemoteArticleDTO`.
- **ArticleIngestService** dedupes via canonical URL hash, upserts, syncs authors/categories.

---

## Requirements
- PHP 8.2+
- Laravel 11 or 12 skeleton (routes/console.php scheduler)
- MySQL 8+ (or compatible)
- Redis (recommended) for locks/rate-limits

---

## Quick start

```bash
cp .env.example .env
# set APP_TIMEZONE, DB_*, and provider keys:
# NEWSAPI_API_KEY=...
# GUARDIAN_API_KEY=...
# NEWSDATA_API_KEY=...

composer install
php artisan key:generate
php artisan migrate
php artisan config:clear && php artisan config:cache
```

---

## Configuration

`config/newshub.php`
```php
return [
    'sources' => [
        'newsdata' => [
            'slug' => 'newsdata',
            'name' => 'NewsData.io',
            'base_url' => 'https://newsdata.io/api/1/latest',
            'api_key' => env('NEWSDATA_DATA_KEY'),
            'default_params' => [
                'q' => 'US blogs',
                'prioritydomain' => 'top',
            ],
        ],
        'newsapi' => [
            'slug' => 'newsapi',
            'name' => 'NewsAPI.org',
            'base_url' => 'https://newsapi.org/v2/everything',
            'api_key' => env('NEWSAPI_API_KEY'),
            'default_params' => [
                'q' => 'blogs',
            ],
        ],
        'guardian' => [
            'slug'  => 'guardian',
            'name'  => 'The Guardian',
            'base_url' => 'https://content.guardianapis.com/search',
            'api_key'  => env('GUARDIAN_API_KEY'),
            'default_params' => [
                'q' => '',
                'page_size' => 10,
            ],
        ],
    ],
];
```

**Providers registered in** `App\Providers\NewsServiceProvider` wire fetchers/normalizers from this config.

---

## Running

### One-off ingest (CLI)
```bash
php artisan news:ingest --source=all --since="-24 hours"
# or only a single source
php artisan news:ingest --source=guardian --since="2025-10-01"
```

- `--source=`: `all | newsdata | newsapi | guardian`
- `--since=`: ISO8601 or relative (e.g., `-2 hours`)

---

## Scheduling (Laravel 11+)

In `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('news:ingest', ['--source' => 'all'])
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/news_ingest.log'))
    ->timezone(config('app.timezone'));
```

Enable the scheduler on your server:

```
* * * * * /usr/bin/php /path/to/your/app/artisan schedule:run >> /dev/null 2>&1
```

> For multi-server deployments, add `->onOneServer()` and use a lock-capable cache (Redis).

---


## Data flow

1) **Fetcher** (`SourceFetcher::fetchSince(?Carbon)`): streams raw provider items with pagination and rate-limits (25 req / 15m).
2) **Normalizer**: transforms each raw item → `RemoteArticleDTO` (consistent fields, ISO 8601 time, ISO-639-1 language).
3) **Service** (`ArticleIngestService::upsertMany()`):
    - compute canonical URL hash
    - skip duplicates
    - upsert article
    - sync authors & categories
    - sanitize fields to column sizes and safe formats

---

## Extending: add a new source

1) Implement a fetcher:
    - Extend `BaseHttpFetcher`
    - Implement: `slug()`, `authHeaders()/authQuery()`, `queryForSince()`, `extractItems()`, `nextPageParams()`
2) Implement a normalizer for that provider → `RemoteArticleDTO`
3) Register in `NewsServiceProvider` factories with config
4) Add credentials to `.env` and defaults to `config/newshub.php`

---

## Resilience & limits
- **Rate limit:** 25 requests / 15 minutes per source (Laravel `RateLimiter`).
- **Circuit breaker:** Open after repeated failures; falls back to last successful JSON response in cache.
- **Sanitization:** Central sanitizer clamps strings and normalizes language (prevents DB “data too long” errors).
- **Per-item isolation:** ingest handles each item inside a try/catch (bad item won’t abort batch).

---


**That’s it!**  
With config keys set and the scheduler running, your app ingests fresh news every 15 minutes, safely and consistently.
