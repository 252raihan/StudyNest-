<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a user with a known password.
     */
    protected function user(string $email = 'student@example.com', string $password = 'study1234'): User
    {
        return User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }

    public function test_guest_can_view_the_login_form(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Login');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = $this->user();

        $this->post('/login', [
            'email' => 'student@example.com',
            'password' => 'study1234',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_does_not_log_plain_text_passwords(): void
    {
        $this->user();

        $this->post('/login', [
            'email' => 'student@example.com',
            'password' => 'study1234',
        ]);

        $log = file_exists(storage_path('logs/laravel.log'))
            ? file_get_contents(storage_path('logs/laravel.log'))
            : '';

        $this->assertStringNotContainsString('study1234', $log);
    }

    public function test_incorrect_password_is_rejected(): void
    {
        $this->user();

        $this->from('/login')
            ->post('/login', [
                'email' => 'student@example.com',
                'password' => 'wrong-password',
            ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_unregistered_email_is_rejected_without_revealing_account_state(): void
    {
        $this->from('/login')
            ->post('/login', [
                'email' => 'nobody@example.com',
                'password' => 'study1234',
            ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->assertSame(
            trans('auth.failed'),
            session('errors')->first('email')
        );
    }

    public function test_wrong_password_returns_the_same_message_as_unknown_email(): void
    {
        $this->user();

        $this->from('/login')->post('/login', [
            'email' => 'student@example.com',
            'password' => 'wrong-password',
        ]);

        $wrongPasswordMessage = session('errors')->first('email');

        $this->from('/login')->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'study1234',
        ]);

        $this->assertSame($wrongPasswordMessage, session('errors')->first('email'));
    }

    public function test_missing_email_is_rejected(): void
    {
        $this->from('/login')
            ->post('/login', ['password' => 'study1234'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_missing_password_is_rejected(): void
    {
        $this->user();

        $this->from('/login')
            ->post('/login', ['email' => 'student@example.com'])
            ->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $this->user();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from('/login')->post('/login', [
                'email' => 'student@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $this->from('/login')
            ->post('/login', [
                'email' => 'student@example.com',
                'password' => 'study1234',
            ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_view_the_login_form(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/login')
            ->assertRedirect(route('dashboard'));
    }
}
