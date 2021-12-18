<?php


namespace App\Http\Requests;


class ListsRequest extends Request
{
    public function rules($action, $data = [])
    {
        switch ($action) {
            case 'create':
                return [
                    'name' => 'bail|required|string|max:255',
                    'is_completed' => 'boolean',
                    'is_closed' => 'boolean',
                ];


            case 'update':
                return [
                    'name' => 'bail|string|max:255',
                    'is_completed' => 'boolean',
                    'is_closed' => 'boolean'
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
