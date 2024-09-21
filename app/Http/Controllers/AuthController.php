<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidCredentialsException;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
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

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->validated())) {
            throw new InvalidCredentialsException('Неверный email или пароль');
        }

        $token = Auth::user()->createToken('auth_token')->plainTextToken;

        $data = array_merge(Auth::user()->toArray(), ['token' => $token]);

        return response()->json($data, 200);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();

        return response()->json(null, 204);
    }
}
