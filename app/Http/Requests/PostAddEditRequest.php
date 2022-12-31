<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostAddEditRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'title'      => 'required|max:150',
            'content'    => 'required|max:3000',
            'cat_id'     => 'required|integer|exists:App\Models\Category,id'
        ];
    }

    /**
     * @return array|string[]
     */
    public function messages(): array
    {
        return [
            'cat_id.exists' => 'The selected cat id is exist',
        ];
    }

    protected function failedValidation(Validator $validator) { 
        
        $response = [
            'message'   => $validator->errors()->first(),
            'errors'      => $validator->errors()->all()
        ];
        throw new HttpResponseException(response()->json($response, 422)); 
    }
}
