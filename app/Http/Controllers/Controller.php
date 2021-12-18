<?php


namespace App\Http\Controllers;


use ArrayObject;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;


class Controller extends BaseController
{
    protected $path;
    protected $model;
    protected $requestModel;

    public function __construct(Request $request)
    {
        $this->path = explode('/', $request->getRequestUri());

        try {
            if (!$this->path[1] == '') {
                $model = 'App\Models\\' . ucfirst($this->path[1]);

                if (!class_exists($model)) {
                    throw new \Exception('Model not found.');
                }

                $this->model = new $model;

                $requestClass = 'App\\' . 'Http\\' . 'Requests\\' . ucfirst($this->path[1]) . 'Request';

                if (!class_exists($requestClass)) {
                    throw new \Exception('Request not found.');
                }

                $this->requestModel = new $requestClass;
            }
        } catch (\Exception $e) {
            print $e->getMessage();
        }
    }

    protected function respondWithToken($token, $refresh, $message)
    {
        return response()->json([
            'data' => [
                'access_token' => $token,
                'refresh_token' => $refresh,
            ],
            'message' => $message,
        ]);
    }

    public function checkModelRules()
    {
        return method_exists($this->requestModel, 'rules');
    }
}
