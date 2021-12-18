<?php


namespace App\Http\Requests;


class UserRequest extends Request
{
    public function rules($action = '', $data = [])
    {
        switch ($action) {
            case 'login':
                return [
                    'email' => 'bail|required|string',
                    'password' => 'bail|required|string',
                ];

            case 'register':
                return [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users|max:255',
                    'password' => 'required|string|max:255'
                ];

            case 'update':
                return [
                    'id' => 'exists:users',
                    'name' => 'bail|required|string|max:255',
                    'email' => 'bail|required|string|unique:users|max:255',
                    'password' => 'bail|required|string|max:255',
                ];

            case 'get-items':
                return [
                    $data['filter'][0] ?? [],
                    $data['order'][0] ?? ['id', 'asc'],
                    $data['withs'] ?? [],
                ];
        }
    }
}
