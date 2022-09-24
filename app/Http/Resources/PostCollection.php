<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\MediaCollection;

class PostCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id'        => $data->id,
                    'title'     => $data->title,
                    'slug'      => $data->slug,
                    'status'    => $data->status,
                    'user_id'   => $data->user_id,
                    'medias'    => $data->media->first(),
                    'tags'      => $data->tags
                ];
            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
    
}
