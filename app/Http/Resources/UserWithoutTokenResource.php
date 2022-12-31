<?php

namespace App\Http\Resources;

use App\Http\Resources\UserCollection;
use App\Http\Resources\MediaCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class UserWithoutTokenResource extends JsonResource
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
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
            'type'  => $this->type,
            'total_post' => $this->when( $this->post_count !== null, fn () => $this->post_count),
            'media' => new MediaCollection($this->whenLoaded('media'))
        ];
    }
}
