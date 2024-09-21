<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

/**
 * @extends Factory<\App\Models\User>
 */
final class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $file = UploadedFile::fake()->image('avatar.png');

        return [
            'email' => fake()->safeEmail,
            'password' => bcrypt(fake()->optional()->password),
            'name' => fake()->name,
            'file' => $file,
        ];
    }
}
