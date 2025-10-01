<?php

namespace App\Domain\NewsHub\Normalizers;

use App\Domain\NewsHub\DTOs\RemoteArticleDTO;
use App\Domain\NewsHub\Sources\Contracts\SourceNormalizer;

final class NewsDataNormalizer implements SourceNormalizer
{
    public function normalizeItem(array $rawItem): RemoteArticleDTO
    {
        $normalizedAuthors = array_values(array_filter(array_map(function (?string $byline) {
            $trimmedByline = trim((string) $byline);
            if ($trimmedByline === '') return null;
            return explode(',', $trimmedByline)[0];
        }, $rawItem['creator'] ?? [])));

        $normalizedContent = $rawItem['content'] ?? null;
        if (is_string($normalizedContent) && str_contains($normalizedContent, 'ONLY AVAILABLE')) {
            $normalizedContent = null;
        }

        $isoPublishedAtUtc = isset($rawItem['pubDate'])
            ? gmdate('c', strtotime(($rawItem['pubDate'] ?? '') . ' ' . ($rawItem['pubDateTZ'] ?? 'UTC')))
            : gmdate('c');

        return new RemoteArticleDTO(
            external_id:   (string) ($rawItem['article_id'] ?? sha1((string)($rawItem['link'] ?? uniqid('', true)))),
            source_slug:   'newsdata',
            title:         (string) ($rawItem['title'] ?? ''),
            summary:                $rawItem['description'] ?? null,
            content:                $normalizedContent,
            url:           (string) ($rawItem['link'] ?? ''),
            image_url:              $rawItem['image_url'] ?? null,
            authors:       $normalizedAuthors,
            categories:    array_map(fn($category) => strtolower(trim((string) $category)), $rawItem['category'] ?? []),
            language:              $rawItem['language'] ?? null,
            published_at:  $isoPublishedAtUtc,
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
