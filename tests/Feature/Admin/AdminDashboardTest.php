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

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_access_the_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Universities');
    }

    public function test_dashboard_shows_correct_counts(): void
    {
        $university = University::factory()->create();
        Department::factory()->count(2)->forUniversity($university)->create();

        // 3 courses across the departments, 2 exams on one course, 4 topics.
        $departments = $university->departments;
        $courses = Course::factory()->count(3)->forDepartment($departments->first())->create();
        $exam = Exam::factory()->forCourse($courses->first())->midterm()->create();
        Exam::factory()->forCourse($courses->first())->final()->create();

        // Explicit orders: the (exam_id, order) unique constraint means random
        // order values could collide.
        foreach ([1, 2, 3, 4] as $order) {
            Topic::factory()->forExam($exam)->create(['order' => $order]);
        }

        $response = $this->actingAs($this->admin())->get('/admin');

        $response->assertOk()->assertViewHas('counts', function (array $counts) {
            return $counts['universities'] === 1
                && $counts['departments'] === 2
                && $counts['courses'] === 3
                && $counts['exams'] === 2
                && $counts['topics'] === 4;
        });
    }

    public function test_dashboard_shows_zero_counts_when_empty(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertViewHas('counts', fn (array $counts) => array_sum($counts) === 0);
    }

    public function test_dashboard_marks_unbuilt_sections_as_coming_soon(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Coming in the next milestone');
    }

    public function test_dashboard_counts_use_database_aggregates_not_php_collections(): void
    {
        University::factory()->count(5)->create();

        // 5 count queries (+ the admin lookup); asserting a tight bound proves
        // records are counted in SQL rather than loaded into PHP.
        $this->expectsDatabaseQueryCount(6);

        $this->actingAs($this->admin())->get('/admin')->assertOk();
    }
}
