<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExtractionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
			'extracted_from' => ['string', 'max:255', 'required'],
			'extracted_from_type' => ['string', 'max:255', 'required'],
			'extraction_result' => ['string', 'max:255', 'required'],
        ];
    }
}
