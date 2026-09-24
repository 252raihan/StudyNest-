<?php

namespace Tests\Feature\Academic;

use App\Models\Course;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Topic;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Walks the full academic chain end to end, the same way the application will
 * later traverse it: University > Department > Course > Exam > Topic.
 */
class RelationshipChainTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_hierarchy_can_be_traversed_forwards(): void
    {
        $university = University::factory()->create([
            'name' => 'Daffodil International University',
            'code' => 'DIU',
        ]);

        $department = Department::factory()->forUniversity($university)->create([
            'name' => 'Computer Science and Engineering',
            'code' => 'CSE',
        ]);

        $course = Course::factory()->forDepartment($department)->create([
            'name' => 'Object Oriented Programming',
            'code' => 'CSE221',
        ]);

        $midterm = Exam::factory()->forCourse($course)->midterm()->create();

        Topic::factory()->forExam($midterm)->create(['name' => 'Class & Object', 'order' => 1]);
        Topic::factory()->forExam($midterm)->create(['name' => 'Constructor', 'order' => 2]);
        Topic::factory()->forExam($midterm)->create(['name' => 'Inheritance', 'order' => 3]);
        $polymorphism = Topic::factory()->forExam($midterm)->create(['name' => 'Polymorphism', 'order' => 4]);

        // University -> Department -> Course -> Exam -> Topic
        $this->assertSame('DIU', $university->departments->first()->university->code);
        $this->assertSame('CSE', $university->departments->first()->code);
        $this->assertSame('CSE221', $university->departments->first()->courses->first()->code);
        $this->assertSame('midterm', $course->exams->first()->type);
        $this->assertSame('Polymorphism', $course->exams->first()->topics->last()->name);
        $this->assertSame($polymorphism->id, $midterm->topics->last()->id);
    }

    public function test_full_hierarchy_can_be_traversed_backwards(): void
    {
        $university = University::factory()->create(['code' => 'DIU']);
        $department = Department::factory()->forUniversity($university)->create(['code' => 'CSE']);
        $course = Course::factory()->forDepartment($department)->create(['code' => 'CSE221']);
        $exam = Exam::factory()->forCourse($course)->midterm()->create();
        $topic = Topic::factory()->forExam($exam)->create(['name' => 'Polymorphism']);

        $this->assertInstanceOf(Exam::class, $topic->exam);
        $this->assertInstanceOf(Course::class, $topic->exam->course);
        $this->assertInstanceOf(Department::class, $topic->exam->course->department);
        $this->assertInstanceOf(University::class, $topic->exam->course->department->university);
        $this->assertSame('DIU', $topic->exam->course->department->university->code);
    }

    public function test_topics_can_be_ordered_under_an_exam(): void
    {
        $exam = Exam::factory()->create();

        Topic::factory()->forExam($exam)->create(['name' => 'Inheritance', 'order' => 3]);
        Topic::factory()->forExam($exam)->create(['name' => 'Class & Object', 'order' => 1]);
        Topic::factory()->forExam($exam)->create(['name' => 'Constructor', 'order' => 2]);

        $ordered = $exam->topics()->orderBy('order')->pluck('name')->all();

        $this->assertSame(['Class & Object', 'Constructor', 'Inheritance'], $ordered);
    }

    public function test_exam_topics_relationship_returns_only_its_own_topics(): void
    {
        $midterm = Exam::factory()->midterm()->create();
        $final = Exam::factory()->final()->create();

        Topic::factory()->forExam($midterm)->create(['name' => 'Inheritance', 'order' => 1]);
        Topic::factory()->forExam($final)->create(['name' => 'File Handling', 'order' => 1]);

        $this->assertCount(1, $midterm->topics);
        $this->assertSame('Inheritance', $midterm->topics->first()->name);
        $this->assertSame('File Handling', $final->topics->first()->name);
    }

    public function test_deleting_a_university_cascades_through_the_whole_chain(): void
    {
        $university = University::factory()->create();
        $department = Department::factory()->forUniversity($university)->create();
        $course = Course::factory()->forDepartment($department)->create();
        $exam = Exam::factory()->forCourse($course)->create();
        $topic = Topic::factory()->forExam($exam)->create();

        $university->delete();

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
        $this->assertDatabaseMissing('exams', ['id' => $exam->id]);
        $this->assertDatabaseMissing('topics', ['id' => $topic->id]);

        $this->assertDatabaseCount('universities', 0);
        $this->assertDatabaseCount('topics', 0);
    }

    public function test_eager_loading_the_chain_loads_the_whole_relationship_tree(): void
    {
        $university = University::factory()->create();
        $department = Department::factory()->forUniversity($university)->create();
        $course = Course::factory()->forDepartment($department)->create();
        $exam = Exam::factory()->forCourse($course)->create();
        Topic::factory()->forExam($exam)->create(['name' => 'Polymorphism']);

        $fresh = University::with('departments.courses.exams.topics')->find($university->id);

        $loadedTopic = $fresh
            ->departments->first()
            ->courses->first()
            ->exams->first()
            ->topics->first();

        $this->assertInstanceOf(Topic::class, $loadedTopic);
        $this->assertSame('Polymorphism', $loadedTopic->name);
        $this->assertTrue($fresh->relationLoaded('departments'));
    }
}
