<?php

namespace App\Domain\NewsHub\Sources\Fetchers;

use App\Domain\NewsHub\Sources\Contracts\SourceFetcher;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class NewsDataFetcher implements SourceFetcher
{
    public function fetchSince(?Carbon $since): iterable
    {
       return [[
          "article_id" => "4acdb159c6bebf82c137ff3aea22f561",
          "title" => "Today’s NYT Pips Hints And Solutions For Wednesday, October 1st",
          "link" => "https://www.forbes.com/sites/erikkain/2025/09/30/todays-nyt-pips-hints-and-solutions-for-wednesday-october-1st/",
          "creator" => ["","Erik Kain, Senior Contributor"],
          "description" => "Looking for help with today's New York Times Pips?...",
          "content" => "ONLY AVAILABLE IN PAID PLANS",
          "pubDate" => "2025-09-30 23:49:50",
          "pubDateTZ" => "UTC",
          "image_url" => "https://imageio.forbes.com/specials-images/imageserve/68ab7aaa205e351d8e7e68df/0x0.jpg?width=960",
          "language" => "english",
          "category" => ["technology"],
      ]];
    }

}
