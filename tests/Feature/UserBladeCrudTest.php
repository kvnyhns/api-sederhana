<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserBladeCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_form_displays_user_and_hobbies(): void
    {
        $user = User::factory()->create(['name' => 'Dewi Suvi']);
        $user->hobbies()->create(['name' => 'Reading']);

        $this->get("/users/{$user->id}/edit")
            ->assertOk()
            ->assertSee('Edit User')
            ->assertSee('Dewi Suvi')
            ->assertSee('Reading');
    }

    public function test_user_update_replaces_hobbies_and_preserves_blank_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Dewi Suvi',
            'email' => 'dewi@example.com',
            'password' => 'original-password',
        ]);
        $user->hobbies()->createMany([
            ['name' => 'Reading'],
            ['name' => 'Writing'],
        ]);
        $originalPassword = $user->fresh()->password;

        $response = $this->put("/users/{$user->id}", [
            'name' => 'Dewi Saras Suvi',
            'email' => 'dewi@example.com',
            'password' => '',
            'password_confirmation' => '',
            'hobbies' => ['  Cycling  ', 'Trail   Running'],
        ]);

        $response->assertRedirect('/users')->assertSessionHas('success');
        $user->refresh();

        $this->assertSame('Dewi Saras Suvi', $user->name);
        $this->assertSame($originalPassword, $user->password);
        $this->assertSame(
            ['Cycling', 'Trail Running'],
            $user->hobbies()->pluck('name')->all(),
        );
    }

    public function test_user_update_can_remove_all_hobbies(): void
    {
        $user = User::factory()->create();
        $user->hobbies()->create(['name' => 'Reading']);

        $this->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'hobbies' => [],
        ])->assertRedirect('/users');

        $this->assertDatabaseCount('hobbies', 0);
    }

    public function test_user_delete_removes_user_and_hobbies(): void
    {
        $user = User::factory()->create();
        $hobby = $user->hobbies()->create(['name' => 'Cycling']);

        $this->get('/users')
            ->assertSee("confirm('Delete this user?')", false);

        $this->delete("/users/{$user->id}")
            ->assertRedirect('/users')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('hobbies', ['id' => $hobby->id]);
    }
}
