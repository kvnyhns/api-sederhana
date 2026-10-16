<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_returns_a_jwt_and_hides_the_password(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Dewi Suvi',
            'email' => 'dewi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email', 'hobbies'],
            ])
            ->assertJsonMissingPath('user.password')
            ->assertJsonPath('user.hobbies', []);
    }

    public function test_invalid_login_returns_a_generic_unauthorized_response(): void
    {
        User::factory()->create([
            'email' => 'dewi@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'dewi@example.com',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_user_crud_requires_a_jwt(): void
    {
        $this->getJson('/api/users')->assertUnauthorized();
    }

    public function test_authenticated_client_can_create_list_update_and_delete_users(): void
    {
        $token = $this->login();

        $createResponse = $this->withToken($token)->postJson('/api/users', [
            'name' => 'Putri Amalia',
            'email' => 'putri@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'hobbies' => ['  Sailing  ', 'Trail   Running'],
        ]);

        $createResponse->assertCreated()->assertJsonPath('data.hobbies.0.name', 'Sailing');
        $userId = $createResponse->json('data.id');

        $this->withToken($token)->getJson('/api/users?per_page=1')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonCount(1, 'data');

        $this->withToken($token)->patchJson("/api/users/{$userId}", [
            'name' => 'Putri Adi Amalia',
            'email' => 'putri@example.com',
        ])->assertOk()->assertJsonPath('data.name', 'Putri Adi Amalia');

        $this->assertDatabaseCount('hobbies', 2);

        $this->withToken($token)->putJson("/api/users/{$userId}", [
            'name' => 'Putri Amalia',
            'email' => 'putri@example.com',
            'hobbies' => ['  Cycling  '],
        ])->assertOk()->assertJsonPath('data.hobbies.0.name', 'Cycling');

        $this->assertDatabaseCount('hobbies', 1);

        $this->withToken($token)->deleteJson("/api/users/{$userId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $userId]);
        $this->assertDatabaseCount('hobbies', 0);
    }

    public function test_logout_invalidates_the_current_token(): void
    {
        $token = $this->login();

        $this->withToken($token)->postJson('/api/auth/logout')->assertNoContent();
        $this->withToken($token)->getJson('/api/users')->assertUnauthorized();
    }

    private function login(): string
    {
        User::factory()->create([
            'email' => 'api@example.com',
            'password' => 'password123',
        ]);

        return (string) $this->postJson('/api/auth/login', [
            'email' => 'api@example.com',
            'password' => 'password123',
        ])->json('token');
    }
}
