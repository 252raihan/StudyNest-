<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Topic;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The safe-deletion flow for universities.
 *
 * Because the foreign keys cascade, deleting a university also removes its
 * departments, courses, exams and topics. These tests lock in the guards that
 * prevent an accidental or forged delete.
 */
class UniversityDeleteSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /**
     * Build a university with a known cascade footprint.
     *
     * @return array{university: University, departments: int, courses: int, exams: int, topics: int}
     */
    protected function universityWithRelations(): array
    {
        $university = University::factory()->create([
            'name' => 'Daffodil International University',
            'code' => 'DIU',
        ]);

        $department = Department::factory()->forUniversity($university)->create();
        $course = Course::factory()->forDepartment($department)->create();
        $midterm = Exam::factory()->forCourse($course)->midterm()->create();
        $final = Exam::factory()->forCourse($course)->final()->create();

        Topic::factory()->forExam($midterm)->create(['order' => 1]);
        Topic::factory()->forExam($midterm)->create(['order' => 2]);
        Topic::factory()->forExam($final)->create(['order' => 1]);
        Topic::factory()->forExam($final)->create(['order' => 2]);

        return [
            'university' => $university,
            'departments' => 1,
            'courses' => 1,
            'exams' => 2,
            'topics' => 4,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Confirmation screen
    |--------------------------------------------------------------------------
    */

    public function test_delete_link_opens_a_confirmation_screen_and_deletes_nothing(): void
    {
        $university = University::factory()->create(['name' => 'Daffodil International University']);

        $this->actingAs($this->admin())
            ->get(route('admin.universities.confirm-delete', $university))
            ->assertOk()
            ->assertSee('Delete University')
            ->assertSee('cannot be undone');

        $this->assertDatabaseHas('universities', ['id' => $university->id]);
    }

    public function test_confirmation_screen_lists_the_cascade_impact(): void
    {
        $fixture = $this->universityWithRelations();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.universities.confirm-delete', $fixture['university']));

        $response->assertOk()->assertViewHas('counts', function (array $counts) use ($fixture) {
            return $counts['departments'] === $fixture['departments']
                && $counts['courses'] === $fixture['courses']
                && $counts['exams'] === $fixture['exams']
                && $counts['topics'] === $fixture['topics'];
        });
    }

    public function test_confirmation_screen_requires_typing_the_university_name(): void
    {
        $university = University::factory()->create(['name' => 'Daffodil International University']);

        $this->actingAs($this->admin())
            ->get(route('admin.universities.confirm-delete', $university))
            ->assertOk()
            ->assertSee('Type the university name to confirm')
            ->assertSee('Daffodil International University');
    }

    /*
    |--------------------------------------------------------------------------
    | Typed-name verification (server-side)
    |--------------------------------------------------------------------------
    */

    public function test_correct_typed_name_allows_deletion(): void
    {
        $university = University::factory()->create(['name' => 'Daffodil International University']);

        $response = $this->actingAs($this->admin())->delete(
            route('admin.universities.destroy', $university),
            ['confirmation_name' => 'Daffodil International University']
        );

        $response->assertRedirect(route('admin.universities.index'));
        $response->assertSessionHas('status', 'University deleted successfully: Daffodil International University');

        $this->assertDatabaseMissing('universities', ['id' => $university->id]);
    }

    public function test_incorrect_typed_name_prevents_deletion(): void
    {
        $university = University::factory()->create(['name' => 'Daffodil International University']);

        $this->actingAs($this->admin())
            ->from(route('admin.universities.confirm-delete', $university))
            ->delete(route('admin.universities.destroy', $university), [
                'confirmation_name' => 'Something Else Entirely',
            ])
            ->assertSessionHasErrors('confirmation_name');

        $this->assertDatabaseHas('universities', ['id' => $university->id]);
    }

    public function test_near_miss_name_prevents_deletion(): void
    {
        $university = University::factory()->create(['name' => 'Daffodil International University']);

        // Missing "International" — a plausible slip that must still be refused.
        $this->actingAs($this->admin())
            ->from(route('admin.universities.confirm-delete', $university))
            ->delete(route('admin.universities.destroy', $university), [
                'confirmation_name' => 'Daffodil University',
            ])
            ->assertSessionHasErrors('confirmation_name');

        $this->assertDatabaseHas('universities', ['id' => $university->id]);
    }

    public function test_missing_confirmation_field_prevents_deletion(): void
    {
        $university = University::factory()->create();

        $this->actingAs($this->admin())
            ->from(route('admin.universities.confirm-delete', $university))
            ->delete(route('admin.universities.destroy', $university), [])
            ->assertSessionHasErrors('confirmation_name');

        $this->assertDatabaseHas('universities', ['id' => $university->id]);
    }

    public function test_confirmation_name_is_trimmed_and_case_insensitive(): void
    {
        $university = University::factory()->create(['name' => 'Dhaka University']);

        $this->actingAs($this->admin())->delete(
            route('admin.universities.destroy', $university),
            ['confirmation_name' => '  dhaka   university  ']
        )->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('universities', ['id' => $university->id]);
    }

    public function test_confirmation_matches_the_target_university_not_the_submitted_value(): void
    {
        $target = University::factory()->create(['name' => 'Target University']);
        $other = University::factory()->create(['name' => 'Other University']);

        // Typing a *different* real university's name must not authorise a
        // delete of the target.
        $this->actingAs($this->admin())
            ->from(route('admin.universities.confirm-delete', $target))
            ->delete(route('admin.universities.destroy', $target), [
                'confirmation_name' => $other->name,
            ])
            ->assertSessionHasErrors('confirmation_name');

        $this->assertDatabaseHas('universities', ['id' => $target->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Cascade behaviour
    |--------------------------------------------------------------------------
    */

    public function test_confirmed_deletion_cascades_to_all_related_records(): void
    {
        $fixture = $this->universityWithRelations();
        $university = $fixture['university'];

        $this->actingAs($this->admin())->delete(
            route('admin.universities.destroy', $university),
            ['confirmation_name' => $university->name]
        );

        $this->assertDatabaseCount('universities', 0);
        $this->assertDatabaseCount('departments', 0);
        $this->assertDatabaseCount('courses', 0);
        $this->assertDatabaseCount('exams', 0);
        $this->assertDatabaseCount('topics', 0);
    }

    public function test_deleting_one_university_leaves_another_intact(): void
    {
        $keep = University::factory()->create(['name' => 'Keep University']);
        Department::factory()->forUniversity($keep)->create();
        $remove = University::factory()->create(['name' => 'Remove University']);

        $this->actingAs($this->admin())->delete(
            route('admin.universities.destroy', $remove),
            ['confirmation_name' => 'Remove University']
        );

        $this->assertDatabaseHas('universities', ['id' => $keep->id]);
        $this->assertSame(1, $keep->departments()->count());
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP semantics and CSRF
    |--------------------------------------------------------------------------
    */

    public function test_get_cannot_delete(): void
    {
        $university = University::factory()->create();

        // GET on the destroy route is not registered at all.
        $this->actingAs($this->admin())
            ->get(route('admin.universities.destroy', $university))
            ->assertStatus(405);

        $this->assertDatabaseHas('universities', ['id' => $university->id]);
    }

    public function test_confirmation_screen_get_does_not_delete(): void
    {
        $university = University::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.universities.confirm-delete', $university))
            ->assertOk();

        $this->assertDatabaseHas('universities', ['id' => $university->id]);
    }

    public function test_guest_cannot_delete(): void
    {
        $university = University::factory()->create();

        $this->delete(route('admin.universities.destroy', $university), [
            'confirmation_name' => $university->name,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('universities', ['id' => $university->id]);
    }

    public function test_student_cannot_delete(): void
    {
        $university = University::factory()->create();
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)->delete(route('admin.universities.destroy', $university), [
            'confirmation_name' => $university->name,
        ])->assertForbidden();

        $this->assertDatabaseHas('universities', ['id' => $university->id]);
    }

    public function test_deleting_a_missing_university_returns_404(): void
    {
        $this->actingAs($this->admin())->delete(
            route('admin.universities.destroy', 999999),
            ['confirmation_name' => 'Anything']
        )->assertNotFound();
    }
}