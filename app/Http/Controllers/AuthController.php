<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidCredentialsException;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\Unauthenticated;

/**
 * @group Authentication
 */
#[Response('{"error": "Нет активной сессии"}', 401)]
class AuthController extends Controller
{
    #[Unauthenticated]
    #[ResponseFromApiResource(UserResource::class, User::class, status: 201)]
    #[Response(
        '{"message": "Переданные данные не корректны","errors": {"email": ["Поле email должно быть уникальным"]}}',
        422
    )]
    public function register(UserRequest $request): JsonResponse
    {
        $values = $request->validated();

        if (isset($values['file'])) {
            $filename = uniqid('avatar_') . '.' . $values['file']->getClientOriginalExtension();
            $path = $values['file']->storeAs('uploads', $filename, 'public');
            $values['file'] = $path;
        }

        $user = User::create($values);

        $token = $user->createToken('auth-token')->plainTextToken;

        return UserResource::make($user)->additional(['token' => $token])->response()->setStatusCode(201);
    }

    #[Response('{"error": "Неверный email или пароль"}', 401)]
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->validated())) {
            throw new InvalidCredentialsException('Неверный email или пароль');
        }

        $token = Auth::user()->createToken('auth_token')->plainTextToken;

        return UserResource::make(Auth::user())->additional(['token' => $token])->response()->setStatusCode(200);
    }

    #[Authenticated]
    #[Response([], status: 204)]
    public function logout(): JsonResponse
    {
        Auth::user()->tokens()->delete();

        return response()->json(null, 204);
    }
}
