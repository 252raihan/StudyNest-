<?php

namespace Tests\Feature\Academic;

use App\Models\Course;
use App\Models\Department;
use App\Models\Exam;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_belongs_to_a_department(): void
    {
        $department = Department::factory()->create();
        $course = Course::factory()->forDepartment($department)->create();

        $this->assertInstanceOf(Department::class, $course->department);
        $this->assertSame($department->id, $course->department->id);
    }

    public function test_department_has_courses(): void
    {
        $department = Department::factory()->create();

        Course::factory()->count(3)->forDepartment($department)->create();

        $this->assertCount(3, $department->courses);
        $this->assertInstanceOf(Course::class, $department->courses->first());
    }

    public function test_course_requires_an_existing_department(): void
    {
        $this->expectException(QueryException::class);

        Course::factory()->create(['department_id' => 999999]);
    }

    public function test_course_code_is_unique_within_a_department(): void
    {
        $department = Department::factory()->create();

        Course::factory()->forDepartment($department)->create(['code' => 'CSE221']);

        $this->expectException(QueryException::class);

        Course::factory()->forDepartment($department)->create(['code' => 'CSE221']);
    }

    public function test_same_course_code_is_allowed_across_departments(): void
    {
        $first = Department::factory()->create();
        $second = Department::factory()->create();

        Course::factory()->forDepartment($first)->create(['code' => 'CSE221']);
        Course::factory()->forDepartment($second)->create(['code' => 'CSE221']);

        $this->assertSame(2, Course::where('code', 'CSE221')->count());
    }

    public function test_semester_and_credit_are_nullable(): void
    {
        $course = Course::factory()->create([
            'semester' => null,
            'credit' => null,
        ]);

        $this->assertNull($course->fresh()->semester);
        $this->assertNull($course->fresh()->credit);
    }

    public function test_credit_supports_fractional_values(): void
    {
        $course = Course::factory()->create(['credit' => 1.5]);

        $this->assertSame('1.5', $course->fresh()->credit);
    }

    public function test_course_name_cannot_be_null(): void
    {
        $department = Department::factory()->create();

        $this->expectException(QueryException::class);

        Course::create([
            'department_id' => $department->id,
            'name' => null,
            'code' => 'NONAME',
        ]);
    }

    public function test_deleting_a_department_cascades_to_its_courses(): void
    {
        $department = Department::factory()->create();
        $course = Course::factory()->forDepartment($department)->create();

        $department->delete();

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_course_has_exams(): void
    {
        $course = Course::factory()->create();

        Exam::factory()->forCourse($course)->midterm()->create();
        Exam::factory()->forCourse($course)->final()->create();

        $this->assertCount(2, $course->exams);
    }
}
