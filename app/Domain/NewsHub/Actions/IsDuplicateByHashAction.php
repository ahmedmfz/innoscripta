<?php

namespace App\Domain\NewsHub\Actions;

use App\Models\Article;

final class IsDuplicateByHashAction
{
    public function __invoke(string $canonicalUrlHash): bool
    {
        return Article::where('canonical_url_hash', $canonicalUrlHash)->exists();
    }
}
