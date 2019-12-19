<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiRequest extends FormRequest
{
    public function failedValidation(Validator $validator )
    {
        throw new HttpResponseException(response(["status"=>40005,"message"=>$validator->errors()->first()],200));
    }
}
