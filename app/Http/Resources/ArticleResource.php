<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\AuthorResource;
use App\Http\Resources\SourceResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
           'id'            => $this->id,
           'title'         => $this->title,
           'summary'       => $this->summary,
           'content'       => $this->content,
           'canonical_url' => $this->canonical_url,
           'image_url'     => $this->image_url,
           'language'      => $this->language,
           'published_at'  => optional($this->published_at)->toIso8601String(),
           'source'        => new SourceResource($this->source),
           'authors'       => AuthorResource::collection($this->authors),
           'categories'    => CategoryResource::collection($this->categories),
        ];
    }
}
