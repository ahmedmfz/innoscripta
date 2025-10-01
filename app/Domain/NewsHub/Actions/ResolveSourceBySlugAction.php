<?php

namespace App\Domain\NewsHub\Actions;

use App\Models\Source;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class ResolveSourceBySlugAction
{
    public function __invoke(string $sourceSlug): Source
    {
        $sourceModel = Source::where('slug', $sourceSlug)->first();
        if (!$sourceModel) {
            throw new ModelNotFoundException("Source not found for slug: {$sourceSlug}");
        }
        return $sourceModel;
    }
}
