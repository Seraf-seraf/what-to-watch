<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function testRegister(): void
    {
        Storage::fake('public');

        $password = 'qwertyqwerty';

        $user = User::factory()->make(
            [
                'password' => Hash::make($password),
            ]
        );

        $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $response = $this->post(route('register'), [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
            'file' => $file,
        ]);

        $token = $response->json('token');

        $response->assertStatus(201)
            ->assertJsonFragment(
                [
                    'user' => [
                        'name' => $user->name,
                        'file' => $user->file,
                        'email' => $user->email,
                        'id' => $user->id
                    ]
                ]
            )
            ->assertJsonFragment(['token' => $token]);

        $user = User::where('email', $user->email)->first();

        $filepath = 'uploads/' . basename($user->file);
        Storage::disk('public')->assertExists($filepath);
    }

    public function testLogin()
    {
        $password = 'qwertyqwerty';
        $user = User::factory()->create(
            [
                'password' => Hash::make($password)
            ]
        );

        $response = $this->post(
            route('login'),
            [
                'email' => $user->email,
                'password' => $password
            ]
        );

        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'file' => $user->file,
                    'token' => $response->json('token')
                ]
            );
    }

    public function testLogout()
    {
        $password = 'qwertyqwerty';
        $user = User::factory()->create(
            [
                'password' => Hash::make($password)
            ]
        );

        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertStatus(204);
    }

    public function testLoginWithWrongPassword()
    {
        $password = 'qwertyqwerty';
        $user = User::factory()->create(
            [
                'password' => Hash::make($password)
            ]
        );

        $response = $this->post(
            route('login'),
            [
                'email' => $user->email,
                'password' => '123'
            ]
        );

        $response->assertStatus(401)
            ->assertJsonFragment(
                [
                    'error' => 'Неверный email или пароль'
                ]
            );
    }

    public function testLogoutWithoutLogin()
    {
        $this->post(route('logout'))
            ->assertJsonFragment(['message' => 'Нет активной сессии'])
            ->assertStatus(401);
    }
}
