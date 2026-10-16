<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserBladeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_listing_displays_users_and_hobbies(): void
    {
        $user = User::factory()->create(['name' => 'Mario Budi']);
        $user->hobbies()->create(['name' => '  Lari   Pagi ']);

        $this->get('/users')
            ->assertOk()
            ->assertSee('Mario Budi')
            ->assertSee('Lari Pagi');
    }

    public function test_public_user_listing_preserves_hobby_creation_order(): void
    {
        $user = User::factory()->create();
        $user->hobbies()->createMany([
            ['name' => 'Jogging'],
            ['name' => 'Swimming'],
            ['name' => 'Gaming'],
        ]);

        $this->get('/users')
            ->assertSeeInOrder(['Jogging', 'Swimming', 'Gaming']);
    }

    public function test_public_create_form_is_available(): void
    {
        $response = $this->get('/users/create');

        $response
            ->assertOk()
            ->assertSee('Create User')
            ->assertSee('password_confirmation')
            ->assertSee('id="add-hobby"', false);

        $this->assertSame(1, substr_count($response->getContent(), 'name="hobbies[]"'));
    }

    public function test_user_and_hobbies_can_be_created_from_blade(): void
    {
        $response = $this->post('/users', [
            'name' => 'Putri Amalia',
            'email' => 'putri@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'hobbies' => ['  Berlayar  ', 'Lari   Pagi'],
        ]);

        $response->assertRedirect('/users')->assertSessionHas('success');

        $user = User::query()->where('email', 'putri@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertSame(
            ['Berlayar', 'Lari Pagi'],
            $user->hobbies()->pluck('name')->all(),
        );
    }

    public function test_create_form_rejects_duplicate_normalized_hobbies(): void
    {
        $this->from('/users/create')
            ->post('/users', [
                'name' => 'Putri Amalia',
                'email' => 'putri@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'hobbies' => ['Berlayar', '  Berlayar  '],
            ])
            ->assertRedirect('/users/create')
            ->assertSessionHasErrors('hobbies');

        $this->assertDatabaseMissing('users', ['email' => 'putri@example.com']);
    }

    public function test_empty_hobby_input_is_ignored(): void
    {
        $this->post('/users', [
            'name' => 'Putri Amalia',
            'email' => 'putri@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'hobbies' => [''],
        ])->assertRedirect('/users');

        $user = User::query()->where('email', 'putri@example.com')->firstOrFail();

        $this->assertCount(0, $user->hobbies);
    }
}
