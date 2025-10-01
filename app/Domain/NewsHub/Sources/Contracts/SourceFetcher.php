<?php

namespace App\Domain\NewsHub\Sources\Contracts;
use Carbon\Carbon;

interface SourceFetcher
{
    /** Stream raw provider items (use yield for low memory) */
    public function fetchSince(?Carbon $since): iterable;
}
