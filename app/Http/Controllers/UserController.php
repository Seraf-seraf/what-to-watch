<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        return UserResource::make($user)->response()->setStatusCode(200);
    }

    public function update(UserRequest $request)
    {
        $user = auth()->user();
        $values = $request->validated();

        if (isset($values['file'])) {
            $filename = uniqid('avatar_') . '.' . $values['file']->getClientOriginalExtension();
            $path = $values['file']->storeAs('uploads', $filename, 'public');
            $values['file'] = $path;
        }

        $user->update($values);

        return response()->json([
            'message' => 'Ваш профиль был обновлен',
            'data' => UserResource::make($user),
        ]);
    }
}
