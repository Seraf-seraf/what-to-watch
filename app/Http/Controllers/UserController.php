<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Response;

/**
 * @group User
 * Просмотр и обновление профиля пользователя
 */
#[Response('{"error": "Нет активной сессии"}', 401)]
#[Response('{"error": "Нет доступа к дейсвтию!"}', 403)]
class UserController extends Controller
{
    #[Authenticated]
    #[Response(
        '{
            "data": {
                "id": 1,
                "name": "guest@guest.com",
                "email": "guest@guest.com",
                "file": "uploads/avatar_66f02fc2ae648.png"
            }
        }'
    )]
    public function show()
    {
        $user = auth()->user();
        return UserResource::make($user);
    }

    #[Authenticated]
    #[Response(
        '{
            "message": "Ваш профиль был обновлен",
            "data": {
                "id": 1,
                "name": "guest@guest.com",
                "email": "guest@guest.com",
                "file": "uploads/avatar_66f02fc2ae648.png"
            }
        }'
    )]
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
