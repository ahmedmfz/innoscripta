<?php

namespace App\Domain\NewsHub\DTOs;

use Carbon\Carbon;

final class RemoteArticleDTO
{
    public function __construct(
        public readonly string  $external_id,
        public readonly string  $source_slug,
        public readonly string  $title,
        public readonly ?string $summary,
        public readonly ?string $content,
        public readonly string  $url,
        public readonly ?string $image_url,
        /** @var string[] */ public readonly array $authors,
        /** @var string[] */ public readonly array $categories,
        public readonly ?string $language,
        public readonly string  $published_at,
        public readonly array   $raw = [],
    ) {}

    public function toArray(): array
    {
        return [
            'external_id'  => $this->external_id,
            'source_slug'  => $this->source_slug,
            'title'        => $this->title,
            'summary'      => $this->summary,
            'content'      => $this->content,
            'url'          => $this->url,
            'image_url'    => $this->image_url,
            'authors'      => $this->authors,
            'categories'   => $this->categories,
            'language'     => $this->language,
            'published_at' => $this->published_at,
            'raw'          => $this->raw,
        ];
    }
}



