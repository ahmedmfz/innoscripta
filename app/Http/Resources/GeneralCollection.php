<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GeneralCollection extends ResourceCollection
{
    public function __construct($resource, ?string $resourceClass = null)
    {
        parent::__construct($resource);

        if ($resourceClass) {
            $this->collects = $resourceClass;
        }
    }


    public function paginationInformation($request, $paginated, $default)
    {
        unset($default['links']);
        unset($default['meta']);
        return $default;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data'                => $this->collects::collection($this->collection)->resolve($request),
            'pagination'          => [
                "current_page"    => $this->currentPage(),
                "total_pages"     =>  $this->lastPage(),
                "per_page"        =>  $this->perPage(),
                "total_items"     =>  $this->total(),
            ],
        ];
    }
}
