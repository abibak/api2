<?php

namespace App\Http\Requests;


use Illuminate\Support\Facades\Validator;

class Request
{
    public function validate($data = [], $action = '')
    {

        $validator = Validator::make($data, $this->rules($action));

        if ($validator->errors()) {
            return $validator->errors()->toArray();
        }

    }
}
