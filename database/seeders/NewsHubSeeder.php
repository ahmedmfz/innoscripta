<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Source;


class NewsHubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $news = [
            ['slug' => 'newsdata', 'name' => 'NewsData.io', 'base_url' => config('news.sources.newsdata.base_url')],
            ['slug' => 'newsapi',  'name' => 'NewsAPI.org', 'base_url' => config('news.sources.newsapi.base_url')],
            ['slug' => 'guardian', 'name' => 'The Guardian', 'base_url' => config('news.sources.guardian.base_url')],
        ];

        foreach ( $news as  $new) {
            Source::updateOrInsert(
            ['slug' => $new['slug']],
             $new
            );
        }
    }
}
