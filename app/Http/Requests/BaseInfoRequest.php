<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaseInfoRequest extends FormRequest
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
            'occupation'=>'required',
            'salary'=>'required',
            'living_place'=>'required',
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'occupation.required' => '礼物ID参数不能为空',
            'salary.required' => '收礼用户ID参数不能为空',
            'living_place.required' => '收礼用户ID参数不能为空',

        ];
    }
}
