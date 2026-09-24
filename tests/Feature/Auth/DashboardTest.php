<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_authenticated_student_can_access_the_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Raihan Ahmed',
            'role' => User::ROLE_STUDENT,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Welcome to StudyNest, Raihan Ahmed')
            ->assertSee('Role: student');
    }

    public function test_authenticated_admin_can_access_the_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Site Admin']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Welcome to StudyNest, Site Admin')
            ->assertSee('Role: admin');
    }
}
