<?php


namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use function PHPUnit\Framework\isType;


class MainController extends Controller
{
    public function login(Request $request)
    {
        $errors = $this->requestModel->validate($request->all(), __FUNCTION__);
        $getUser = User::where('email', $request->input('email'))->first();

        if (!empty($errors) && !$getUser) {
            return response()->json($errors);
        }

        if (!$token = JWTAuth::attempt($request->only(['email', 'password']))) {
            return response()->json(['error' => 'User is not found!'], 401);
        }

        $refresh = $getUser->generateRefreshToken();
        return $this->respondWithToken($token, $refresh, 'Logged!');
    }

    public function register(Request $request)
    {
        $errors = $this->requestModel->validate($request->all(), __FUNCTION__);

        if (empty($errors)) {
            User::create($request->all());

            return response()->json([
                'data' => null,
                'message' => 'Registered!',
            ]);
        }

        return response()->json($errors, 422);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'message' => 'Logout!'
        ]);
    }

    public function refreshAccessToken(Request $request)
    {
        $user = User::where('refresh_token', $request->input('refresh_token'))->first();

        if ($user) {
            $newToken = auth()->refresh(true, true);
            return $this->respondWithToken($newToken, $user->generateRefreshToken(), 'Refreshed!');
        }
    }

    public function create(Request $request)
    {
        if ($this->checkModelRules()) {
            $errors = $this->requestModel->validate($request->input('attributes') ?? [], __FUNCTION__);

            if (empty($errors)) {
                $created = $this->model::create($request->input('attributes'));

                return response()->json([
                    'data' => [
                        'attributes' => $created->find($created->id),
                    ],
                    'message' => 'Created!',
                ], 201);
            }

            return response()->json($errors, 422);
        }
    }

    public function delete($id)
    {
        if (is_numeric($id) && is_object($this->model)) {
            $getDeleted = $this->model::find($id);
            $delete = ($getDeleted) ? $getDeleted->delete() : null;

            return response()->json([
                'data' => null,
                'message' => ($delete === null) ? 'No such record was found in the database.' : 'Deleted!',
            ], ($delete === null) ? 422 : 200);
        }

        return response()->json([
            'data' => null,
            'message' => 'Error!',
        ], 404);
    }

    public function update(Request $request, $id)
    {
        if (is_numeric($id) && is_object($this->model)) {
            $getData = $request->input('attributes') ?? [];
            $updated = $this->model->find($id);

            $errors = $this->requestModel->validate($getData, __FUNCTION__);

            if (empty($errors)) {
                $updateModel = ($updated) ? $updated->update($getData) : null;

                return response()->json([
                    'data' => [
                        'attributes' => $updated,
                    ],
                    'message' => ($updateModel) ? 'Updated!' : 'No such record was found in the database.',
                ], ($updateModel === null) ? 422 : 200);
            }
            return response()->json($errors, 422);
        }

        return response()->json([
            'data' => null,
            'message' => 'Error!',
        ]);
    }

    public function getItem(Request $request, $id)
    {
        if (is_numeric($id)) {
            $getData = $this->model::find($id);

            $receivedData = ($getData) ? $getData::where('id', $id)
                ->with($request->input('withs') ?? [])
                ->first() : null;

            return response()->json([
                'data' => [
                    'attributes' => $receivedData,
                ],
                'message' => ($receivedData === null) ? 'No such record was found in the database.' : 'Received!',
            ], ($receivedData === null) ? 422 : 200);
        }

        return response()->json([
            'data' => [
                'attributes' => null,
            ],
            'message' => 'Error!',
        ], 404);
    }

    public function getItems(Request $request)
    {
        if ($this->checkModelRules()) {
            $rules = $this->requestModel->rules('get-items', $request->all());

            $filter = $this->model->checkAttribute($rules[0][0] ?? '', $rules[0][1] ?? '', $rules[0][2] ?? '');
            $order = $this->model->checkAttribute($rules[1][0], $rules[1][1]);

            if ($rules[0] == [] || $order[0] == []) {
                $rules[0] = [];
                $order[0] = 'id';
            } else {
                $rules[0] = [$rules[0]];
            }

            $receivedData = $this->model
                ::where([$filter])
                ->orderBy($order[0], $order[1] ?? 'asc')
                ->with($rules[2] ?? [])
                ->paginate((int)$request->input('per_page'))
                ->values();

            return response()->json([
                'data' => [
                    'items' => $receivedData,
                ],
                'message' => 'Received!',
            ]);
        }
    }
}
