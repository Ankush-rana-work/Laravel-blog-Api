<?php

namespace App\Http\Resources;

use App\Http\Resources\PostSingleResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CommentCollection extends ResourceCollection
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
                    'user'      => $data->user,
                    'post_id'      => $data->post_id,
                    'parent_id' => $data->parent_id,
                    'text' => $data->text,
                    'children'  => $data->children,
                    'media'     => $data->media->first(),
                ];
            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
