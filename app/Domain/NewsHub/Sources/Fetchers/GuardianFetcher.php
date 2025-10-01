<?php

namespace App\Domain\NewsHub\Sources\Fetchers;

use App\Domain\NewsHub\Sources\Contracts\SourceFetcher;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GuardianFetcher implements SourceFetcher
{
    public function fetchSince(?Carbon $since): iterable
    {
         return [[
            "id" => "food/2025/sep/26/toffee-apple-pudding-cake-recipe-helen-goh",
            "type" => "article",
            "sectionName" => "Food",
            "webPublicationDate" => "2025-09-26T05:00:57Z",
            "webTitle" => "Helen Goh’s recipe for toffee apple pudding cake | The sweet spot",
            "webUrl" => "https://www.theguardian.com/food/2025/sep/26/toffee-apple-pudding-cake-recipe-helen-goh",
            "pillarName" => "Lifestyle"
        ]];
    }

}
