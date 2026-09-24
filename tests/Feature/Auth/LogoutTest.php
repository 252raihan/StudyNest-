<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_logout_invalidates_the_authentication_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_logout_route_is_not_reachable_over_get(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/logout')
            ->assertStatus(405);

        $this->assertAuthenticated();
    }

    public function test_guest_cannot_logout(): void
    {
        $this->post('/logout')->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
