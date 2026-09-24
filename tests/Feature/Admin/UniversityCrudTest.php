<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Department;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversityCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_view_the_university_list(): void
    {
        University::factory()->create([
            'name' => 'Daffodil International University',
            'code' => 'DIU',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.universities.index'))
            ->assertOk()
            ->assertSee('Daffodil International University')
            ->assertSee('DIU')
            ->assertSee('Add University');
    }

    public function test_university_list_is_paginated(): void
    {
        University::factory()->count(20)->create();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.universities.index'));

        $response->assertOk()->assertViewHas('universities', function ($universities) {
            // PER_PAGE is 15, so 20 records must span more than one page.
            return $universities->count() === 15 && $universities->total() === 20;
        });
    }

    public function test_university_list_can_be_searched(): void
    {
        University::factory()->create(['name' => 'Daffodil International University', 'code' => 'DIU']);
        University::factory()->create(['name' => 'Dhaka University', 'code' => 'DU']);

        $this->actingAs($this->admin())
            ->get(route('admin.universities.index', ['search' => 'Daffodil']))
            ->assertOk()
            ->assertSee('Daffodil International University')
            ->assertDontSee('Dhaka University');
    }

    public function test_empty_list_shows_a_helpful_message(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.universities.index'))
            ->assertOk()
            ->assertSee('No universities found');
    }

    /*
    |--------------------------------------------------------------------------
    | Create / Store
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_open_the_create_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.universities.create'))
            ->assertOk()
            ->assertSee('Add University')
            ->assertSee('Code');
    }

    public function test_admin_can_create_a_university(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.universities.store'), [
            'name' => 'Daffodil International University',
            'code' => 'DIU',
            'status' => '1',
        ]);

        $university = University::where('code', 'DIU')->firstOrFail();

        $response->assertRedirect(route('admin.universities.show', $university));
        $response->assertSessionHas('status', 'University created successfully.');

        $this->assertDatabaseHas('universities', [
            'name' => 'Daffodil International University',
            'code' => 'DIU',
            'status' => true,
        ]);
    }

    public function test_status_is_stored_as_inactive_when_selected(): void
    {
        $this->actingAs($this->admin())->post(route('admin.universities.store'), [
            'name' => 'Inactive University',
            'code' => 'INACT',
            'status' => '0',
        ]);

        $this->assertDatabaseHas('universities', ['code' => 'INACT', 'status' => false]);
    }

    public function test_code_is_normalised_to_upper_case(): void
    {
        $this->actingAs($this->admin())->post(route('admin.universities.store'), [
            'name' => 'Case Test University',
            'code' => '  diu  ',
            'status' => '1',
        ]);

        $this->assertDatabaseHas('universities', ['code' => 'DIU']);
    }

    public function test_missing_name_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.universities.create'))
            ->post(route('admin.universities.store'), [
                'name' => '',
                'code' => 'NONAME',
                'status' => '1',
            ])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('universities', 0);
    }

    public function test_missing_code_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.universities.create'))
            ->post(route('admin.universities.store'), [
                'name' => 'No Code University',
                'code' => '',
                'status' => '1',
            ])
            ->assertSessionHasErrors('code');

        $this->assertDatabaseCount('universities', 0);
    }

    public function test_duplicate_code_is_rejected(): void
    {
        University::factory()->create(['code' => 'DIU']);

        $this->actingAs($this->admin())
            ->from(route('admin.universities.create'))
            ->post(route('admin.universities.store'), [
                'name' => 'Another University',
                'code' => 'DIU',
                'status' => '1',
            ])
            ->assertSessionHasErrors('code');

        $this->assertDatabaseCount('universities', 1);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.universities.create'))
            ->post(route('admin.universities.store'), [
                'name' => 'Bad Status University',
                'code' => 'BADST',
                'status' => 'maybe',
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('universities', 0);
    }

    public function test_overlong_name_and_code_are_rejected(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.universities.create'))
            ->post(route('admin.universities.store'), [
                'name' => str_repeat('a', 256),
                'code' => str_repeat('b', 51),
                'status' => '1',
            ])
            ->assertSessionHasErrors(['name', 'code']);

        $this->assertDatabaseCount('universities', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_view_university_details(): void
    {
        $university = University::factory()->create([
            'name' => 'Daffodil International University',
            'code' => 'DIU',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.universities.show', $university))
            ->assertOk()
            ->assertSee('Daffodil International University')
            ->assertSee('DIU')
            ->assertSee('Active')
            ->assertSee('Related Records');
    }

    public function test_show_page_reports_accurate_relationship_counts(): void
    {
        $university = University::factory()->create();
        Department::factory()->count(3)->forUniversity($university)->create();
        Course::factory()->count(2)->forDepartment($university->departments->first())->create();

        $this->actingAs($this->admin())
            ->get(route('admin.universities.show', $university))
            ->assertOk()
            ->assertViewHas('counts', fn (array $counts) => $counts['departments'] === 3 && $counts['courses'] === 2);
    }

    public function test_missing_university_returns_404(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.universities.show', 999999))
            ->assertNotFound();
    }

    /*
    |--------------------------------------------------------------------------
    | Edit / Update
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_open_the_edit_form(): void
    {
        $university = University::factory()->create([
            'name' => 'Daffodil International University',
            'code' => 'DIU',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.universities.edit', $university))
            ->assertOk()
            ->assertSee('Edit University')
            ->assertSee('Daffodil International University')
            ->assertSee('DIU');
    }

    public function test_admin_can_update_a_university(): void
    {
        $university = University::factory()->create(['name' => 'Old Name', 'code' => 'OLD']);

        $response = $this->actingAs($this->admin())->put(route('admin.universities.update', $university), [
            'name' => 'New Name',
            'code' => 'NEW',
            'status' => '0',
        ]);

        $response->assertRedirect(route('admin.universities.show', $university));
        $response->assertSessionHas('status', 'University updated successfully.');

        $this->assertDatabaseHas('universities', [
            'id' => $university->id,
            'name' => 'New Name',
            'code' => 'NEW',
            'status' => false,
        ]);
    }

    public function test_existing_code_remains_valid_when_updating_the_same_university(): void
    {
        $university = University::factory()->create(['code' => 'DIU']);

        // Same code, changed name — must not trip the unique rule on itself.
        $this->actingAs($this->admin())
            ->put(route('admin.universities.update', $university), [
                'name' => 'Renamed University',
                'code' => 'DIU',
                'status' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.universities.show', $university));

        $this->assertSame('Renamed University', $university->fresh()->name);
    }

    public function test_another_universitys_code_cannot_be_used_on_update(): void
    {
        University::factory()->create(['code' => 'TAKEN']);
        $university = University::factory()->create(['code' => 'MINE']);

        $this->actingAs($this->admin())
            ->from(route('admin.universities.edit', $university))
            ->put(route('admin.universities.update', $university), [
                'name' => $university->name,
                'code' => 'TAKEN',
                'status' => '1',
            ])
            ->assertSessionHasErrors('code');

        $this->assertSame('MINE', $university->fresh()->code);
    }

    public function test_invalid_update_is_rejected(): void
    {
        $university = University::factory()->create(['name' => 'Keep Me']);

        $this->actingAs($this->admin())
            ->from(route('admin.universities.edit', $university))
            ->put(route('admin.universities.update', $university), [
                'name' => '',
                'code' => '',
                'status' => '1',
            ])
            ->assertSessionHasErrors(['name', 'code']);

        $this->assertSame('Keep Me', $university->fresh()->name);
    }

    /*
    |--------------------------------------------------------------------------
    | Mass assignment protection
    |--------------------------------------------------------------------------
    */

    public function test_mass_assignment_cannot_change_protected_fields_on_create(): void
    {
        $this->actingAs($this->admin())->post(route('admin.universities.store'), [
            'name' => 'Mass Assignment Test',
            'code' => 'MAT1',
            'status' => '1',
            'id' => 9999,
            'created_at' => '2000-01-01 00:00:00',
            'updated_at' => '2000-01-01 00:00:00',
        ]);

        $university = University::where('code', 'MAT1')->firstOrFail();

        $this->assertNotSame(9999, $university->id);
        $this->assertNotSame('2000-01-01 00:00:00', $university->created_at->toDateTimeString());
    }

    public function test_mass_assignment_cannot_change_the_id_on_update(): void
    {
        $university = University::factory()->create(['code' => 'KEEPID']);
        $originalId = $university->id;

        $this->actingAs($this->admin())->put(route('admin.universities.update', $university), [
            'name' => 'Updated',
            'code' => 'KEEPID',
            'status' => '1',
            'id' => 9999,
        ]);

        $this->assertSame($originalId, $university->fresh()->id);
        $this->assertDatabaseMissing('universities', ['id' => 9999]);
    }

    public function test_role_is_not_a_university_attribute(): void
    {
        $this->actingAs($this->admin())->post(route('admin.universities.store'), [
            'name' => 'Role Injection University',
            'code' => 'ROLEINJ',
            'status' => '1',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('universities', ['code' => 'ROLEINJ']);
        $this->assertDatabaseMissing('universities', ['role' => 'admin']);
    }

    public function test_unauthenticated_post_is_rejected(): void
    {
        $this->post(route('admin.universities.store'), [
            'name' => 'Ghost University',
            'code' => 'GHOST',
            'status' => '1',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('universities', 0);
    }
}