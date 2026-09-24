<?php

namespace Tests\Feature\Academic;

use App\Models\Course;
use App\Models\Exam;
use App\Models\Topic;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_belongs_to_a_course(): void
    {
        $course = Course::factory()->create();
        $exam = Exam::factory()->forCourse($course)->midterm()->create();

        $this->assertInstanceOf(Course::class, $exam->course);
        $this->assertSame($course->id, $exam->course->id);
    }

    public function test_course_has_exams(): void
    {
        $course = Course::factory()->create();

        Exam::factory()->count(2)->forCourse($course)->create();

        $this->assertCount(2, $course->exams);
    }

    public function test_exam_requires_an_existing_course(): void
    {
        $this->expectException(QueryException::class);

        Exam::factory()->create(['course_id' => 999999]);
    }

    public function test_exam_supports_midterm_type(): void
    {
        $exam = Exam::factory()->midterm()->create();

        $this->assertSame(Exam::TYPE_MIDTERM, $exam->type);
        $this->assertContains($exam->type, Exam::TYPES);
    }

    public function test_exam_supports_final_type(): void
    {
        $exam = Exam::factory()->final()->create();

        $this->assertSame(Exam::TYPE_FINAL, $exam->type);
        $this->assertContains($exam->type, Exam::TYPES);
    }

    public function test_exam_type_is_not_locked_to_a_fixed_set_schema_wise(): void
    {
        // A university-specific exam type must be storable without a migration.
        $exam = Exam::factory()->create(['type' => 'quiz']);

        $this->assertSame('quiz', $exam->fresh()->type);
    }

    public function test_one_exam_per_type_per_course(): void
    {
        $course = Course::factory()->create();

        Exam::factory()->forCourse($course)->create([
            'type' => Exam::TYPE_MIDTERM,
            'title' => 'Midterm Examination',
        ]);

        $this->expectException(QueryException::class);

        // A second midterm on the same course must be rejected by the
        // unique (course_id, type) constraint.
        Exam::factory()->forCourse($course)->create([
            'type' => Exam::TYPE_MIDTERM,
            'title' => 'Duplicate Midterm',
        ]);
    }

    public function test_same_exam_type_is_allowed_on_different_courses(): void
    {
        Exam::factory()->midterm()->create();
        Exam::factory()->midterm()->create();

        $this->assertSame(2, Exam::where('type', Exam::TYPE_MIDTERM)->count());
    }

    public function test_exam_title_and_type_cannot_be_null(): void
    {
        $course = Course::factory()->create();

        $this->expectException(QueryException::class);

        Exam::create([
            'course_id' => $course->id,
            'type' => null,
            'title' => 'Nameless',
        ]);
    }

    public function test_deleting_a_course_cascades_to_its_exams(): void
    {
        $course = Course::factory()->create();
        $exam = Exam::factory()->forCourse($course)->create();

        $course->delete();

        $this->assertDatabaseMissing('exams', ['id' => $exam->id]);
    }

    public function test_exam_has_topics(): void
    {
        $exam = Exam::factory()->create();

        Topic::factory()->count(3)->forExam($exam)->create();

        $this->assertCount(3, $exam->topics);
    }
}
