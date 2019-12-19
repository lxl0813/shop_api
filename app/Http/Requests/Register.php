<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;



class Register extends ApiRequest
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
            "user_phone" => 'bail|required',
            "user_pwd"   => 'bail|required',
            "user_repwd" => 'same:user_pwd',
            "code"       => 'bail|required'
        ];
    }

    public function messages()
    {
        return [
            "user_phone.required"  =>  "请输入手机号码或者邮箱",
            "user_pwd.required"    =>  "密码不能为空",
            "user_repwd.same"      =>  "请确保两次密码一致",
            "code.required"        =>  "验证码不能为空"
        ];
    }

}
