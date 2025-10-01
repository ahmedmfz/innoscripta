<?php

namespace App\Domain\NewsHub\Sources\Fetchers;

use App\Domain\NewsHub\Sources\Contracts\SourceFetcher;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class NewsApiFetcher implements SourceFetcher
{
    public function fetchSince(?Carbon $since): iterable
    {
        return [[
           "source" => ["id" => null, "name" => "Jetgirl.art"],
           "author" => null,
           "title" => "Blogs Used to Be Different",
           "description" => "I saw someone earlier post about how intrusive it felt...",
           "url" => "https://jetgirl.art/blogs-used-to-be-very-different/",
           "urlToImage" => "https://bear-images.sfo2.cdn.digitaloceanspaces.com/herman-1683556668-0.png",
           "publishedAt" => "2025-09-07T00:45:22Z",
           "content" => "06 Sep, 2025\nI saw someone earlier post..."
       ]];
    }

}
