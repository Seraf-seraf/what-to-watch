<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function testSetAsAdmin()
    {
        $user = User::factory()->create();

        $user->setAsAdmin();

        $role = Role::query()->where('name', 'admin')->first();

        $this->assertContains($user->id, $role->users->pluck('id'));
        $this->assertDatabaseHas(
            'users_roles',
            [
            'user_id' => $user->id,
            'role_id' => $role->id,
            ]
        );
    }
}
