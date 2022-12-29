<?php

namespace App\Http\Resources;

use App\Http\Resources\MediaResource;
use App\Http\Resources\TagsSearchCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class PostSingleResource extends JsonResource
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
            'id'        => $this->id,
            'title'     => $this->title,
            'slug'      => $this->slug,
            'status'    => $this->status,
            'user_id'   => new UserWithoutTokenResource($this->whenLoaded('user')),
            'media'     => new MediaCollection($this->whenLoaded('media')),
            'tags'      => new TagsSearchCollection($this->whenLoaded('tags')),
        ];
    }
}
