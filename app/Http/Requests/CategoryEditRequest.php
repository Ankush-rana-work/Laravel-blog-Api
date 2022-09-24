<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryEditRequest extends FormRequest
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
       // echo $this->slug;die();
        return [
            'name'  => 'required|max:100',
            'slug'  => 'required|unique:categories,slug,'.$this->cat_id
        ];
    }
}
