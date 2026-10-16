<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::query()->with('hobbies')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user->load('hobbies'),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
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
        });

        return to_route('users.index')->with('success', 'User created successfully.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
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

        return to_route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return to_route('users.index')->with('success', 'User deleted successfully.');
    }
}
