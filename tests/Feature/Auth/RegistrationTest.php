<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_registration_form(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Create your account');
    }

    public function test_valid_registration_creates_a_user_and_authenticates_them(): void
    {
        $response = $this->post('/register', [
            'name' => 'Raihan Ahmed',
            'email' => 'raihan@example.com',
            'password' => 'study1234',
            'password_confirmation' => 'study1234',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'name' => 'Raihan Ahmed',
            'email' => 'raihan@example.com',
        ]);

        $this->assertAuthenticated();
    }

    public function test_new_registrations_receive_the_student_role(): void
    {
        $this->post('/register', [
            'name' => 'Student User',
            'email' => 'student@example.com',
            'password' => 'study1234',
            'password_confirmation' => 'study1234',
        ]);

        $user = User::where('email', 'student@example.com')->firstOrFail();

        $this->assertSame(User::ROLE_STUDENT, $user->role);
        $this->assertTrue($user->isStudent());
        $this->assertFalse($user->isAdmin());
    }

    public function test_role_is_not_mass_assignable_on_the_user_model(): void
    {
        $this->assertNotContains('role', (new User)->getFillable());
    }

    public function test_user_cannot_assign_themselves_the_admin_role(): void
    {
        $this->post('/register', [
            'name' => 'Sneaky Student',
            'email' => 'sneaky@example.com',
            'password' => 'study1234',
            'password_confirmation' => 'study1234',
            'role' => User::ROLE_ADMIN,
        ]);

        $user = User::where('email', 'sneaky@example.com')->firstOrFail();

        $this->assertSame(User::ROLE_STUDENT, $user->role);
        $this->assertFalse($user->isAdmin());
    }

    public function test_stored_password_is_hashed_and_never_plain_text(): void
    {
        $this->post('/register', [
            'name' => 'Hashed User',
            'email' => 'hashed@example.com',
            'password' => 'study1234',
            'password_confirmation' => 'study1234',
        ]);

        $user = User::where('email', 'hashed@example.com')->firstOrFail();

        $this->assertNotSame('study1234', $user->password);
        $this->assertNotSame('study1234', $user->getRawOriginal('password'));
        $this->assertStringStartsWith('$2y$', $user->getRawOriginal('password'));
        $this->assertTrue(password_verify('study1234', $user->getRawOriginal('password')));
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->from('/register')
            ->post('/register', [
                'name' => 'Duplicate User',
                'email' => 'taken@example.com',
                'password' => 'study1234',
                'password_confirmation' => 'study1234',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->from('/register')
            ->post('/register', [
                'name' => 'Invalid Email',
                'email' => 'not-an-email',
                'password' => 'study1234',
                'password_confirmation' => 'study1234',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_name_is_required(): void
    {
        $this->from('/register')
            ->post('/register', [
                'name' => '',
                'email' => 'noname@example.com',
                'password' => 'study1234',
                'password_confirmation' => 'study1234',
            ])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_weak_password_is_rejected(): void
    {
        $this->from('/register')
            ->post('/register', [
                'name' => 'Weak Password',
                'email' => 'weak@example.com',
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->from('/register')
            ->post('/register', [
                'name' => 'Mismatch User',
                'email' => 'mismatch@example.com',
                'password' => 'study1234',
                'password_confirmation' => 'different1234',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_authenticated_user_cannot_view_the_registration_form(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/register')
            ->assertRedirect(route('dashboard'));
    }
}
