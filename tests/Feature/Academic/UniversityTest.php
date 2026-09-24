<?php

namespace Tests\Feature\Academic;

use App\Models\University;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversityTest extends TestCase
{
    use RefreshDatabase;

    public function test_university_can_be_created(): void
    {
        $university = University::create([
            'name' => 'Daffodil International University',
            'code' => 'DIU',
            'status' => true,
        ]);

        $this->assertDatabaseHas('universities', [
            'id' => $university->id,
            'name' => 'Daffodil International University',
            'code' => 'DIU',
        ]);

        $this->assertTrue($university->status);
    }

    public function test_university_code_must_be_unique(): void
    {
        University::factory()->create(['code' => 'DIU']);

        $this->expectException(QueryException::class);

        University::factory()->create(['code' => 'DIU']);
    }

    public function test_university_name_cannot_be_null(): void
    {
        $this->expectException(QueryException::class);

        University::create([
            'name' => null,
            'code' => 'NULLNAME',
        ]);
    }

    public function test_university_code_cannot_be_null(): void
    {
        $this->expectException(QueryException::class);

        University::create([
            'name' => 'No Code University',
            'code' => null,
        ]);
    }

    public function test_status_defaults_to_true(): void
    {
        $university = University::create([
            'name' => 'Default Status University',
            'code' => 'DSU',
        ]);

        $this->assertTrue($university->fresh()->status);
    }

    public function test_mass_assignment_ignores_unknown_attributes(): void
    {
        $university = University::create([
            'name' => 'Guarded University',
            'code' => 'GU',
            'id' => 9999,
        ]);

        $this->assertNotSame(9999, $university->id);
    }
}
