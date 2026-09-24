<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Authorization for every /admin route, exercised across all role combinations.
 *
 * The admin middleware is the single authorization mechanism reused from
 * Milestone 2 — these tests prove it guards the new routes too.
 */
class AdminRouteAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every admin route as a [method, uri] pair.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function adminRoutes(): array
    {
        return [
            'dashboard' => ['get', '/admin'],
            'universities index' => ['get', '/admin/universities'],
            'universities create' => ['get', '/admin/universities/create'],
            'universities store' => ['post', '/admin/universities'],
            'universities show' => ['get', '/admin/universities/1'],
            'universities edit' => ['get', '/admin/universities/1/edit'],
            'universities update' => ['put', '/admin/universities/1'],
            'universities confirm delete' => ['get', '/admin/universities/1/delete'],
            'universities destroy' => ['delete', '/admin/universities/1'],
        ];
    }

    #[DataProvider('adminRoutes')]
    public function test_guest_is_redirected_to_login(string $method, string $uri): void
    {
        $this->{$method}($uri)->assertRedirect(route('login'));
    }

    #[DataProvider('adminRoutes')]
    public function test_student_receives_403(string $method, string $uri): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->{$method}($uri)
            ->assertForbidden();
    }

    #[DataProvider('adminRoutes')]
    public function test_admin_is_not_blocked_by_authorization(string $method, string $uri): void
    {
        $admin = User::factory()->admin()->create();

        // 403 and 302-to-login are both authorization failures. An admin must
        // never see either — any other status proves the middleware passed.
        $status = $this->actingAs($admin)->{$method}($uri)->getStatusCode();

        $this->assertNotSame(403, $status, "Admin was forbidden from {$method} {$uri}");
    }

    public function test_student_cannot_reach_admin_area_even_though_link_is_hidden(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        // The link is hidden...
        $this->actingAs($student)
            ->get('/dashboard')
            ->assertDontSee(route('admin.dashboard'));

        // ...but typing the URL directly is still blocked server-side.
        $this->actingAs($student)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
