<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserListRequest extends FormRequest
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
            'type' => 'required|in:admin,user',
            'sort_field_name'=> 'in:name,id,email,is_is_verfied',
            'page' => 'required|integer|gt:0'
        ];
    }

    public function messages()
    {
        return [
            'type.in' => 'The selected type must be admin or user',
            'sort_field_name.in'=> 'The selecte sort column must be name, id, email and is_is_verfied'
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
