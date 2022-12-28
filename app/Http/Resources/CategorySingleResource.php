<?php

namespace App\Http\Resources;

use App\Http\Resources\MediaResource;
use App\Http\Resources\CategoryCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class CategorySingleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {   
        return [
            "id"            => $this->id,
            "name"          => $this->name,
            "slug"          => $this->slug,
            "parent_id"     => intval($this->parent_id),
            "image"         => new MediaResource($this->media->first()),
            'children'      => new CategoryCollection($this->whenLoaded('children')),
        ];
    }
}
