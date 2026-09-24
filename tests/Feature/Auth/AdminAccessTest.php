<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_the_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('StudyNest');
    }

    public function test_student_receives_403_on_the_admin_dashboard(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_from_the_admin_dashboard(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_privilege_escalation_attempt_does_not_grant_admin_access(): void
    {
        // A public registration that tries to self-assign the admin role.
        $this->post('/register', [
            'name' => 'Escalation Attempt',
            'email' => 'escalate@example.com',
            'password' => 'study1234',
            'password_confirmation' => 'study1234',
            'role' => User::ROLE_ADMIN,
        ]);

        $user = User::where('email', 'escalate@example.com')->firstOrFail();

        $this->assertSame(User::ROLE_STUDENT, $user->role);

        $this->get('/admin')->assertForbidden();
    }

    public function test_admin_link_is_hidden_from_students_but_visible_to_admins(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee(route('admin.dashboard'));

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee(route('admin.dashboard'));
    }

    public function test_admin_link_does_not_appear_for_guests(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee(route('admin.dashboard'));
    }
}
