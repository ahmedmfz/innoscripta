<?php

namespace App\Domain\NewsHub\Normalizers;

use App\Domain\NewsHub\DTOs\RemoteArticleDTO;
use App\Domain\NewsHub\Sources\Contracts\SourceNormalizer;

final class NewsApiNormalizer implements SourceNormalizer
{
    public function normalizeItem(array $rawItem): RemoteArticleDTO
    {
        $articleUrl = (string) ($rawItem['url'] ?? '');
        $authorName = $rawItem['author'] ?? null;

        return new RemoteArticleDTO(
            external_id:   $articleUrl !== '' ? sha1($articleUrl) : sha1(json_encode($rawItem)),
            source_slug:   'newsapi',
            title:         (string) ($rawItem['title'] ?? ''),
            summary:       $rawItem['description'] ?? null,
            content:       $rawItem['content'] ?? null,
            url:           $articleUrl,
            image_url:     $rawItem['urlToImage'] ?? null,
            authors:       $authorName ? [trim((string) $authorName)] : [],
            categories:    [],
            language:      $rawItem['language'] ?? null,
            published_at: (string) ($rawItem['publishedAt'] ?? gmdate('c')),
            raw:           $rawItem,
        );
    }

    public function normalizeMany(iterable $rawItems): array
    {
        $dtoList = [];
        foreach ($rawItems as $rawItem) {
            $dtoList[] = $this->normalizeItem((array) $rawItem);
        }
        return $dtoList;
    }
}
