<?php

namespace App\Http\Resources;

use App\Http\Resources\UserWithoutTokenResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentSingleResource extends JsonResource
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
            'id'            => $this->id,
            'user_id'       => new UserWithoutTokenResource($this->user),
            'post_id'       => new PostSingleResource($this->post),
            'text'          => $this->text,
            'parent_id'     => $this->parent_id,
            'media'         => new MediaCollection($this->media),
            'total_comment' => $this->children->count()
        ];
    }
}
