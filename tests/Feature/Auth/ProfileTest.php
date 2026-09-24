<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_the_profile_page(): void
    {
        $this->get('/profile')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_the_profile_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Raihan Ahmed',
            'email' => 'raihan@example.com',
            'role' => User::ROLE_STUDENT,
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Raihan Ahmed')
            ->assertSee('raihan@example.com')
            ->assertSee('student');
    }

    public function test_profile_never_exposes_the_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk()
            ->assertDontSee($user->getRawOriginal('password'), false);
    }
}
