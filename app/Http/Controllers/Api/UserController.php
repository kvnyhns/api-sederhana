<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request): JsonResource
    {
        $perPage = $request->integer('per_page', 15);
        abort_if($perPage < 1 || $perPage > 100, 422, 'The per_page must be between 1 and 100.');

        return UserResource::collection(
            User::query()->with('hobbies')->latest()->paginate($perPage),
        );
    }

    public function store(StoreUserRequest $request): UserResource
    {
        $data = $request->validated();
        $user = DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $user->hobbies()->createMany(
                array_map(
                    fn (string $name): array => ['name' => $name],
                    $data['hobbies'] ?? [],
                ),
            );

            return $user;
        });

        return new UserResource($user->load('hobbies'));
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->load('hobbies'));
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $user): void {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            if (filled($data['password'] ?? null)) {
                $user->update(['password' => $data['password']]);
            }

            if (array_key_exists('hobbies', $data)) {
                $user->hobbies()->delete();
                $user->hobbies()->createMany(
                    array_map(
                        fn (string $name): array => ['name' => $name],
                        $data['hobbies'],
                    ),
                );
            }
        });

        return new UserResource($user->fresh()->load('hobbies'));
    }

    public function destroy(User $user): Response
    {
        $user->delete();

        return response()->noContent();
    }
}
