<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTableRequest extends FormRequest
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
			'name' => ['string', 'max:255', 'required'],
			'description' => ['string', 'required'],
			'fields' => ['array', 'required'],
			'field_types' => ['array', 'required'],
			'field_indexed' => ['array', 'required'],
        ];
    }
}
