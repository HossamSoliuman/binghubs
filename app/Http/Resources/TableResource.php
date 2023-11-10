<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TableResource extends JsonResource
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
            'id'=> $this->id,
			'name' => $this->name,
			'description' => $this->description,
			'fields' => $this->fields,
            'created_at' => $this->created_at,
            'last_update' => $this->updated_at,
        ];
    }
}
