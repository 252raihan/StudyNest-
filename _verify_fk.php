<?php

/**
 * Live FK integrity checks against MySQL. Each check runs in a transaction
 * that is rolled back, so no data is permanently changed.
 *
 * Run with:  php _verify_fk.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Course;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Topic;
use App\Models\University;
use Illuminate\Support\Facades\DB;

$results = [];

function check(string $label, bool $pass, string $detail = ''): void
{
    global $results;
    $results[] = ['label' => $label, 'pass' => $pass, 'detail' => $detail];
}

/** Attempt an insert expected to fail on a foreign key violation. */
function expectFkRejection(string $label, callable $insert): void
{
    try {
        DB::beginTransaction();
        $insert();
        DB::rollBack();
        check($label, false, 'insert unexpectedly SUCCEEDED');
    } catch (Illuminate\Database\QueryException $e) {
        DB::rollBack();
        $isFk = str_contains($e->getMessage(), 'foreign key constraint')
            || str_contains($e->getMessage(), 'Integrity constraint');
        check($label, $isFk, $isFk ? 'rejected' : 'rejected for a non-FK reason: ' . substr($e->getMessage(), 0, 80));
    }
}

// ---- Baseline is untouched ------------------------------------------------
$before = [
    'universities' => University::count(),
    'departments' => Department::count(),
    'courses' => Course::count(),
    'exams' => Exam::count(),
    'topics' => Topic::count(),
];

// ---- Orphan rejection ----------------------------------------------------
expectFkRejection('departments.university_id -> nonexistent rejected', fn() => DB::table('departments')->insert([
    'university_id' => 999999,
    'name' => 'Orphan',
    'code' => 'ORPH',
    'status' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]));

expectFkRejection('courses.department_id -> nonexistent rejected', fn() => DB::table('courses')->insert([
    'department_id' => 999999,
    'name' => 'Orphan',
    'code' => 'ORPH',
    'status' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]));

expectFkRejection('exams.course_id -> nonexistent rejected', fn() => DB::table('exams')->insert([
    'course_id' => 999999,
    'type' => 'midterm',
    'title' => 'Orphan',
    'created_at' => now(),
    'updated_at' => now(),
]));

expectFkRejection('topics.exam_id -> nonexistent rejected', fn() => DB::table('topics')->insert([
    'exam_id' => 999999,
    'name' => 'Orphan',
    'order' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]));

// ---- NOT NULL rejection --------------------------------------------------
function expectNullRejection(string $label, callable $insert): void
{
    try {
        DB::beginTransaction();
        $insert();
        DB::rollBack();
        check($label, false, 'insert unexpectedly SUCCEEDED');
    } catch (Illuminate\Database\QueryException $e) {
        DB::rollBack();
        check($label, true, 'rejected');
    }
}

expectNullRejection('universities.name NOT NULL enforced', fn() => DB::table('universities')->insert([
    'name' => null,
    'code' => 'NULLX',
    'status' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]));

expectNullRejection('exams.type NOT NULL enforced', fn() => DB::table('exams')->insert([
    'course_id' => Course::first()->id,
    'type' => null,
    'title' => 'Nameless',
    'created_at' => now(),
    'updated_at' => now(),
]));

expectNullRejection('topics.name NOT NULL enforced', fn() => DB::table('topics')->insert([
    'exam_id' => Exam::first()->id,
    'name' => null,
    'order' => 99,
    'created_at' => now(),
    'updated_at' => now(),
]));

// ---- Nullable columns actually accept null -------------------------------
try {
    DB::beginTransaction();
    $courseId = DB::table('courses')->insertGetId([
        'department_id' => Department::first()->id,
        'name' => 'Nullable Course',
        'code' => 'NULLABLE1',
        'semester' => null,
        'credit' => null,
        'status' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $row = DB::table('courses')->where('id', $courseId)->first();
    DB::rollBack();
    check('courses.semester / credit accept NULL', $row->semester === null && $row->credit === null, 'accepted');
} catch (Throwable $e) {
    DB::rollBack();
    check('courses.semester / credit accept NULL', false, $e->getMessage());
}

// ---- Unique constraint ---------------------------------------------------
expectNullRejection('duplicate universities.code rejected', fn() => DB::table('universities')->insert([
    'name' => 'Duplicate Code University',
    'code' => 'DIU',
    'status' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]));

// ---- Cascade delete (rolled back) ---------------------------------------
try {
    DB::beginTransaction();

    $u = University::create(['name' => 'Temp Cascade University', 'code' => 'TMPCASCADE']);
    $d = Department::create(['university_id' => $u->id, 'name' => 'Temp Dept', 'code' => 'TD']);
    $c = Course::create(['department_id' => $d->id, 'name' => 'Temp Course', 'code' => 'TC1']);
    $e = Exam::create(['course_id' => $c->id, 'type' => 'midterm', 'title' => 'Temp Exam']);
    $t = Topic::create(['exam_id' => $e->id, 'name' => 'Temp Topic', 'order' => 1]);

    $u->delete();

    $gone = ! University::find($u->id)
        && ! Department::find($d->id)
        && ! Course::find($c->id)
        && ! Exam::find($e->id)
        && ! Topic::find($t->id);

    DB::rollBack();

    check('DELETE university cascades to all 4 child tables', $gone, $gone ? 'all children removed' : 'orphans remained');
} catch (Throwable $e) {
    DB::rollBack();
    check('DELETE university cascades to all 4 child tables', false, $e->getMessage());
}

// ---- Baseline unchanged --------------------------------------------------
$after = [
    'universities' => University::count(),
    'departments' => Department::count(),
    'courses' => Course::count(),
    'exams' => Exam::count(),
    'topics' => Topic::count(),
];

check('Baseline row counts unchanged after checks', $before === $after, json_encode($after));

// ---- Report --------------------------------------------------------------
echo PHP_EOL;
printf("%-56s %s\n", 'CHECK', 'RESULT');
echo str_repeat('-', 78) . PHP_EOL;

$failed = 0;
foreach ($results as $r) {
    if (! $r['pass']) {
        $failed++;
    }
    printf("%-56s %-6s %s\n", $r['label'], $r['pass'] ? 'PASS' : 'FAIL', $r['detail']);
}

$total = count($results);
echo str_repeat('-', 78) . PHP_EOL;
printf("TOTAL: %d   PASSED: %d   FAILED: %d\n\n", $total, $total - $failed, $failed);

exit($failed === 0 ? 0 : 1);
