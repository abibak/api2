<?php


namespace App\Http\Requests;


class TaskRequest extends Request
{
    public function rules($action, $data = [])
    {
        switch ($action) {
            case 'create':
                return [
                    'name' => 'bail|required|string|max:255',
                    'list_id' => 'bail|required|integer|exists:lists,id',
                    'description' => 'bail|string|',
                    'urgency' => 'bail|required|integer',
                ];
            case 'update':
                return [
                    'name' => 'bail|string|max:255',
                    'list_id' => 'bail|integer|exists:lists,id',
                    'description' => 'string',
                    'urgency' => 'bail|integer',
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
