<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    public function testUserShowProfile()
    {
        $response = $this->get(route('profile'));

        $response
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                ]
            );
    }

    public function testUpdateProfile()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->patch(
            route('profile.update'),
            [
            'name' => 'Serafim 22',
            'file' => $file,
            'email' => 'serafim123serafim12313@mail.ru'
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                'message' => 'Ваш профиль был обновлен',
                'data' => [
                    'id' => $this->user->id,
                    'file' => $file,
                    'name' => 'Serafim 22',
                    'email' => 'serafim123serafim12313@mail.ru'
                ]
                ]
            );

        $this->assertDatabaseHas(
            User::class,
            [
            'id' => $this->user->id,
            'name' => 'Serafim 22',
            ]
        );
        $user = User::find($this->user->id);
        Storage::disk('public')->assertExists($user->file);
    }

    public function testUpdateProfileWithRepeatableEmail()
    {
        User::factory()->create(
            [
            'email' => 'factory2@mail.ru'
            ]
        );

        $response = $this->patch(
            route('profile.update'),
            [
            'name' => 'Serafim 2',
            'email' => 'factory2@mail.ru'
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJson(
                [
                'message' => 'Переданные данные не корректны',
                'errors' => [
                    'email' => [
                        'Поле Email должно быть уникальным'
                    ]
                ]
                ]
            );
    }

    public function testUpdateProfileWithoutUser()
    {
        Auth::logout();

        $response = $this->patch(
            route('profile.update'),
            [
            'name' => 'Serafim 2',
            'email' => 'factory2@mail.ru'
            ]
        );

        $response->assertStatus(401);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $password = '12345678';
        $this->user = User::factory()->create(
            [
            'password' => Hash::make($password)
            ]
        );

        $this->post(
            route(
                'login',
                [
                'email' => $this->user->email,
                'password' => $password
                ]
            )
        );
    }
}
