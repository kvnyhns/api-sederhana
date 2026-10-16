<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(fn (): User => User::create($data));
        $token = auth('api')->login($user);

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->load('hobbies')),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json(['token' => $token]);
    }

    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json(null, 204);
    }
}
