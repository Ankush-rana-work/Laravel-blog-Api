<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
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
            'name'          => $this->name,
            'file_name'     => $this->file_name,
            'model_type'    => $this->model_type,
            'model_id'      => $this->model_id,
            'uuid'          => $this->uuid,
            'original_url'  => $this->original_url,
            'size'          => $this->size,
            'mime_type'     => $this->mime_type,
        ];
    }
}
