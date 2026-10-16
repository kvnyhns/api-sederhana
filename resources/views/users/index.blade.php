<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Users</title>
    </head>
    <body>
        <main>
            <h1>Users</h1>

            @if (session('success'))
                <p role="status">{{ session('success') }}</p>
            @endif

            <p><a href="{{ route('users.create') }}">Create User</a></p>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Hobbies</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @forelse ($user->hobbies as $hobby)
                                    <span>{{ $hobby->name }}</span>@if (! $loop->last), @endif
                                @empty
                                    <span>No hobbies</span>
                                @endforelse
                            </td>
                            <td>
                                <a href="{{ route('users.edit', $user) }}">Edit</a>
                                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </main>
    </body>
</html>