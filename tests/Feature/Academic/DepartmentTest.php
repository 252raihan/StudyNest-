<?php

namespace Tests\Feature\Academic;

use App\Models\Department;
use App\Models\University;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_belongs_to_a_university(): void
    {
        $university = University::factory()->create();
        $department = Department::factory()->forUniversity($university)->create();

        $this->assertInstanceOf(University::class, $department->university);
        $this->assertSame($university->id, $department->university->id);
    }

    public function test_university_has_departments(): void
    {
        $university = University::factory()->create();

        Department::factory()->count(3)->forUniversity($university)->create();

        $this->assertCount(3, $university->departments);
        $this->assertInstanceOf(Department::class, $university->departments->first());
        $this->assertTrue($university->departments->every(
            fn (Department $department) => $department->university_id === $university->id
        ));
    }

    public function test_department_requires_an_existing_university(): void
    {
        $this->expectException(QueryException::class);

        Department::create([
            'university_id' => 999999,
            'name' => 'Orphan Department',
            'code' => 'ORPH',
        ]);
    }

    public function test_department_cannot_reference_a_university_that_does_not_exist(): void
    {
        $this->expectException(QueryException::class);

        Department::factory()->create(['university_id' => 999999]);
    }

    public function test_department_code_is_unique_within_a_university(): void
    {
        $university = University::factory()->create();

        Department::factory()->forUniversity($university)->create(['code' => 'CSE']);

        $this->expectException(QueryException::class);

        Department::factory()->forUniversity($university)->create(['code' => 'CSE']);
    }

    public function test_same_department_code_is_allowed_across_different_universities(): void
    {
        $first = University::factory()->create();
        $second = University::factory()->create();

        Department::factory()->forUniversity($first)->create(['code' => 'CSE']);
        Department::factory()->forUniversity($second)->create(['code' => 'CSE']);

        $this->assertDatabaseCount('departments', 2);
        $this->assertSame(2, Department::where('code', 'CSE')->count());
    }

    public function test_deleting_a_university_cascades_to_its_departments(): void
    {
        $university = University::factory()->create();
        $department = Department::factory()->forUniversity($university)->create();

        $university->delete();

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    public function test_department_name_and_code_cannot_be_null(): void
    {
        $university = University::factory()->create();

        $this->expectException(QueryException::class);

        Department::create([
            'university_id' => $university->id,
            'name' => null,
            'code' => 'NN',
        ]);
    }
}
