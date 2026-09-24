<?php

namespace Tests\Feature\Academic;

use App\Models\Exam;
use App\Models\Topic;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicTest extends TestCase
{
    use RefreshDatabase;

    public function test_topic_belongs_to_an_exam(): void
    {
        $exam = Exam::factory()->create();
        $topic = Topic::factory()->forExam($exam)->create();

        $this->assertInstanceOf(Exam::class, $topic->exam);
        $this->assertSame($exam->id, $topic->exam->id);
    }

    public function test_exam_has_topics(): void
    {
        $exam = Exam::factory()->create();

        Topic::factory()->count(3)->forExam($exam)->create();

        $this->assertCount(3, $exam->topics);
        $this->assertInstanceOf(Topic::class, $exam->topics->first());
    }

    public function test_topic_requires_an_existing_exam(): void
    {
        $this->expectException(QueryException::class);

        Topic::factory()->create(['exam_id' => 999999]);
    }

    public function test_topic_description_is_nullable(): void
    {
        $topic = Topic::factory()->create(['description' => null]);

        $this->assertNull($topic->fresh()->description);
    }

    public function test_topic_name_cannot_be_null(): void
    {
        $exam = Exam::factory()->create();

        $this->expectException(QueryException::class);

        Topic::create([
            'exam_id' => $exam->id,
            'name' => null,
            'order' => 1,
        ]);
    }

    public function test_topic_order_must_be_unique_within_an_exam(): void
    {
        $exam = Exam::factory()->create();

        Topic::factory()->forExam($exam)->create(['order' => 1]);

        $this->expectException(QueryException::class);

        Topic::factory()->forExam($exam)->create(['order' => 1]);
    }

    public function test_same_topic_order_is_allowed_across_exams(): void
    {
        Topic::factory()->create(['order' => 1]);
        Topic::factory()->create(['order' => 1]);

        $this->assertSame(2, Topic::where('order', 1)->count());
    }

    public function test_order_is_cast_to_integer(): void
    {
        $topic = Topic::factory()->create(['order' => 4]);

        $this->assertIsInt($topic->fresh()->order);
    }

    public function test_deleting_an_exam_cascades_to_its_topics(): void
    {
        $exam = Exam::factory()->create();
        $topic = Topic::factory()->forExam($exam)->create();

        $exam->delete();

        $this->assertDatabaseMissing('topics', ['id' => $topic->id]);
    }
}
