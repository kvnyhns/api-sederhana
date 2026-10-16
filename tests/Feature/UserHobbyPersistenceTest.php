<?php

namespace Tests\Feature;

use App\Models\User;
use App\Rules\DistinctHobbyNames;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UserHobbyPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_hobby_names_are_normalized_before_persistence(): void
    {
        $user = User::factory()->create();

        $hobby = $user->hobbies()->create([
            'name' => '  Lari   Pagi  ',
        ]);

        $this->assertSame('Lari Pagi', $hobby->name);
    }

    public function test_application_validation_rejects_duplicate_normalized_hobby_names(): void
    {
        $validator = Validator::make(
            ['hobbies' => ['  Lari Pagi ', 'Lari   Pagi']],
            ['hobbies' => [new DistinctHobbyNames]],
        );

        $this->assertTrue($validator->fails());
    }

    public function test_database_rejects_duplicate_hobby_names_for_the_same_user(): void
    {
        $user = User::factory()->create();
        $user->hobbies()->create(['name' => 'Reading']);

        $this->expectException(QueryException::class);

        $user->hobbies()->create(['name' => 'Reading']);
    }

    public function test_different_users_may_share_a_hobby_name(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $firstUser->hobbies()->create(['name' => 'Reading']);
        $secondUser->hobbies()->create(['name' => 'Reading']);

        $this->assertDatabaseCount('hobbies', 2);
    }

    public function test_deleting_a_user_deletes_their_hobbies(): void
    {
        $user = User::factory()->create();
        $hobby = $user->hobbies()->create(['name' => 'Cycling']);

        $user->delete();

        $this->assertDatabaseMissing('hobbies', ['id' => $hobby->id]);
    }
}
