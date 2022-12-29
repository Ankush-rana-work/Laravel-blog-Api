<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
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
            'name'     => 'required|max:100',
            'email'    => 'required|email|unique:users|max:100',
            'password' => 'required|max:20|confirmed',
            'password_confirmation' => 'required|max:20'
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
