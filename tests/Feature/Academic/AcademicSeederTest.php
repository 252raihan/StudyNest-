<?php

namespace Tests\Feature\Academic;

use App\Models\Course;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Topic;
use App\Models\University;
use Database\Seeders\AcademicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies the development seed data matches the specification and that
 * re-running the seeder does not create duplicates.
 */
class AcademicSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_specified_academic_hierarchy(): void
    {
        $this->seed(AcademicSeeder::class);

        $university = University::where('code', 'DIU')->firstOrFail();
        $this->assertSame('Daffodil International University', $university->name);

        $department = Department::where('university_id', $university->id)
            ->where('code', 'CSE')
            ->firstOrFail();
        $this->assertSame('Computer Science and Engineering', $department->name);

        $this->assertDatabaseHas('courses', [
            'department_id' => $department->id,
            'code' => 'CSE221',
            'name' => 'Object Oriented Programming',
        ]);
        $this->assertDatabaseHas('courses', [
            'department_id' => $department->id,
            'code' => 'CSE123',
            'name' => 'Data Structures',
        ]);
        $this->assertDatabaseHas('courses', [
            'department_id' => $department->id,
            'code' => 'MAT102',
            'name' => 'Mathematics',
        ]);
    }

    public function test_seeder_creates_midterm_and_final_exams_for_cse221(): void
    {
        $this->seed(AcademicSeeder::class);

        $course = Course::where('code', 'CSE221')->firstOrFail();

        $this->assertSame(2, $course->exams()->count());
        $this->assertDatabaseHas('exams', [
            'course_id' => $course->id,
            'type' => Exam::TYPE_MIDTERM,
        ]);
        $this->assertDatabaseHas('exams', [
            'course_id' => $course->id,
            'type' => Exam::TYPE_FINAL,
        ]);
    }

    public function test_seeder_creates_the_specified_midterm_topics_in_order(): void
    {
        $this->seed(AcademicSeeder::class);

        $exam = Exam::where('type', Exam::TYPE_MIDTERM)
            ->whereHas('course', fn ($query) => $query->where('code', 'CSE221'))
            ->firstOrFail();

        $this->assertSame(
            ['Class & Object', 'Constructor', 'Inheritance', 'Polymorphism'],
            $exam->topics()->orderBy('order')->pluck('name')->all()
        );
    }

    public function test_seeder_creates_the_specified_final_topics_in_order(): void
    {
        $this->seed(AcademicSeeder::class);

        $exam = Exam::where('type', Exam::TYPE_FINAL)
            ->whereHas('course', fn ($query) => $query->where('code', 'CSE221'))
            ->firstOrFail();

        $this->assertSame(
            ['Exception Handling', 'Collections', 'File Handling'],
            $exam->topics()->orderBy('order')->pluck('name')->all()
        );
    }

    public function test_seeder_is_idempotent_and_creates_no_duplicates(): void
    {
        $this->seed(AcademicSeeder::class);
        $this->seed(AcademicSeeder::class);
        $this->seed(AcademicSeeder::class);

        $this->assertSame(1, University::count());
        $this->assertSame(1, Department::count());
        $this->assertSame(3, Course::count());
        $this->assertSame(2, Exam::count());
        $this->assertSame(7, Topic::count());
    }

    public function test_seeder_does_not_delete_existing_records(): void
    {
        $unrelated = Course::factory()->create(['code' => 'UNRELATED']);

        $this->seed(AcademicSeeder::class);

        $this->assertDatabaseHas('courses', ['id' => $unrelated->id]);
    }

    public function test_the_full_chain_resolves_after_seeding(): void
    {
        $this->seed(AcademicSeeder::class);

        $topic = Topic::where('name', 'Polymorphism')->firstOrFail();

        $this->assertSame('CSE221', $topic->exam->course->code);
        $this->assertSame('CSE', $topic->exam->course->department->code);
        $this->assertSame('DIU', $topic->exam->course->department->university->code);
    }
}
