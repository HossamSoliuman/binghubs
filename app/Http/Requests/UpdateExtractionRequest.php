<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExtractionRequest extends FormRequest
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
			'extracted_from' => ['string', 'max:255', 'nullable'],
			'extracted_from_type' => ['string', 'max:255', 'nullable'],
			'extraction_result' => ['string', 'max:255', 'nullable'],
        ];
    }
}
