@if ($errors->any())
    <div role="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $hobbies = $user?->hobbies->pluck('name')->all() ?? [''];
    $oldHobbies = old('hobbies', $hobbies);
@endphp

<form method="POST" action="{{ $formAction }}">
    @csrf
    @if ($formMethod !== 'POST')
        @method($formMethod)
    @endif

    <label for="name">Name</label>
    <input id="name" name="name" value="{{ old('name', $user?->name) }}" required>

    <label for="email">Email</label>
    <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required>

    <label for="password">Password{{ $user ? ' (leave blank to keep unchanged)' : '' }}</label>
    <input id="password" name="password" type="password" {{ $user ? '' : 'required' }}>

    <label for="password_confirmation">Confirm password</label>
    <input id="password_confirmation" name="password_confirmation" type="password" {{ $user ? '' : 'required' }}>

    <fieldset>
        <legend>Hobbies</legend>
        <div id="hobby-inputs">
            @foreach ($oldHobbies as $hobby)
                <input name="hobbies[]" value="{{ $hobby }}">
            @endforeach
            @if ($user)
                <input name="hobbies[]" value="">
            @endif
        </div>
        <button type="button" id="add-hobby">Add hobby</button>
    </fieldset>

    <button type="submit">{{ $submitLabel }}</button>
</form>

<script>
    document.getElementById('add-hobby').addEventListener('click', function () {
        const input = document.createElement('input');
        input.name = 'hobbies[]';
        input.type = 'text';
        document.getElementById('hobby-inputs').appendChild(input);
    });
</script>