<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\IngestNewsDataJob;


Schedule::command('news:ingest', ['--source' => 'all'])
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/news_ingest.log'))
    ->timezone(config('app.timezone'));
