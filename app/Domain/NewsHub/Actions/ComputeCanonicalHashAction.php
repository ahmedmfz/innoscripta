<?php

namespace App\Domain\NewsHub\Actions;

use App\Domain\NewsHub\Support\UrlHelper;

final class ComputeCanonicalHashAction
{
    public function __invoke(string $originalUrl): string
    {
        return UrlHelper::canonicalHash($originalUrl);
    }
}
