<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data()
    {
        $userData = [
            'username' => 'newUser',
            'email' => 'new@example.com',
            'password' => 'password',
            'role' => 'user'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Register success')
            ->assertJsonPath('status', true);
    }

    public function test_user_cannot_register_with_duplicate_email()
    {
        $user = User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'username' => 'newUser',
            'email' => 'existing@example.com', // Duplicate email
            'password' => 'password',
            'role' => 'user'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Validation error')
            ->assertJsonValidationErrors('email');
    }

    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $loginData = [
            'email' => 'user@example.com',
            'password' => 'password',
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['data' => ['accessToken']]);
    }

    public function test_user_cannot_login_with_incorrect_credentials()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('correctpassword'),
        ]);

        $loginData = [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Invalid credentials')
            ->assertJsonPath('status', false);
    }

    public function test_authenticated_user_can_retrieve_own_profile()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/auth/user');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('status', true);
    }
}
