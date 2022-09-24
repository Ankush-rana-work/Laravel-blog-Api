<?php

namespace App\Http\Resources;

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
            "id"        => $this->id,
            "user_id"   => $this->user_id,
            "post_id"   => $this->post_id,
            "text"      => $this->text,
            "parent_id" => $this->parent_id,
            "media"     => $this->media()->first(),
        ];
    }
}
