<?php

namespace App\Domain\NewsHub\Actions;

use App\Domain\NewsHub\DTOs\RemoteArticleDTO;

final class BuildArticlePayloadAction
{
    /**
     * Shape the DB payload from the DTO + computed hash.
     */
    public function __invoke(RemoteArticleDTO $dto, int $sourceId, string $canonicalUrlHash): array
    {
        return [
            'source_id'          => $sourceId,
            'external_id'        => $dto->external_id,
            'title'              => $dto->title,
            'summary'            => $dto->summary,
            'content'            => $dto->content,
            'canonical_url'                => $dto->url,                 // keep 'url' (not 'canonical_url')
            'image_url'          => $dto->image_url,
            'language'           => $dto->language,
            'published_at'       => $dto->published_at,
            'canonical_url_hash' => $canonicalUrlHash,
            'raw'                => $dto->raw,                 // keep array; Article casts 'raw' => 'array'
        ];
    }
}
