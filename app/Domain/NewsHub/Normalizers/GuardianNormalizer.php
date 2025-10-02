<?php

namespace App\Domain\NewsHub\Normalizers;

use App\Domain\NewsHub\DTOs\RemoteArticleDTO;
use App\Domain\NewsHub\Sources\Contracts\SourceNormalizer;

final class GuardianNormalizer implements SourceNormalizer
{
    public function normalizeItem(array $rawItem): RemoteArticleDTO
    {
        $normalizedCategories = [];
        if (!empty($rawItem['sectionName'])) $normalizedCategories[] = strtolower((string) $rawItem['sectionName']);
//         if (!empty($rawItem['pillarName']))  $normalizedCategories[] = strtolower((string) $rawItem['pillarName']);
        $normalizedCategories = array_values(array_unique(array_filter($normalizedCategories)));

        return new RemoteArticleDTO(
            external_id:   (string) ($rawItem['id'] ?? sha1(json_encode($rawItem))),
            source_slug:   'guardian',
            title:         (string) ($rawItem['webTitle'] ?? ''),
            summary:       null,
            content:       null,
            url:           (string) ($rawItem['webUrl'] ?? ''),
            image_url:     null,
            authors:       [],
            categories:    $normalizedCategories,
            language:      $rawItem['language'] ?? null,
            published_at:  (string) ($rawItem['webPublicationDate'] ?? gmdate('c')),
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
