<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\Promo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoTest extends TestCase
{
    use RefreshDatabase;

    public function testPromoIndex(): void
    {
        $film = Film::factory()->create();
        $promo = Promo::factory()->create(['film_id' => $film->id]);

        $response = $this->get(route('promo.index'));
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $promo->id]);
    }

    public function testPromoStore()
    {
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->roles()->attach($adminRole);

        $promoFilm = Film::factory()->create();

        $this->actingAs($user);
        $response = $this->post(route('set.promo', ['film' => $promoFilm->id]));

        $response->assertStatus(201);
        $this->assertDatabaseHas('promo', ['film_id' => $promoFilm->id]);
    }

    public function testPromoAccessForbidden()
    {
        $user = User::factory()->create();

        $promoFilm = Film::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('set.promo', ['film' => $promoFilm->id]));
        $response->assertStatus(403);

        $response = $this->post(route('delete.promo', ['film' => $promoFilm->id]));
        $response->assertStatus(403);
    }

    public function testPromoDelete()
    {
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->roles()->attach($adminRole);
        $this->actingAs($user);

        $promoFilm = Film::factory()->create();
        Promo::factory()->create(['film_id' => $promoFilm->id]);

        $this->assertDatabaseHas('promo', ['film_id' => $promoFilm->id]);

        $response = $this->delete(route('delete.promo', ['film' => $promoFilm->id]));
        $response->assertStatus(204);

        $this->assertDatabaseMissing('promo', ['film_id' => $promoFilm->id]);
    }

    public function testPromoAlreadyCreated()
    {
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->roles()->attach($adminRole);

        $promoFilm = Film::factory()->create();

        $this->actingAs($user)
            ->post(route('set.promo', ['film' => $promoFilm->id]));

        $response = $this->post(route('set.promo', ['film' => $promoFilm->id]));

        $response->assertStatus(422);
    }
}
