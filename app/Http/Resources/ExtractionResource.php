<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExtractionResource extends JsonResource
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
			'extracted_from' => $this->extracted_from,
			'extracted_from_type' => $this->extracted_from_type,
			'extraction_result' => $this->extraction_result,
            'created_at' => $this->created_at,
            'last_update' => $this->updated_at,
        ];
    }
}
