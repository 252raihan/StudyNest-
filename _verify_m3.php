<?php

/**
 * Read-only verification of the Milestone 3 schema and seeded data in MySQL.
 * Run with:  php _verify_m3.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\University;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo 'Connection : ' . DB::connection()->getDatabaseName() . ' (' . config('database.default') . ')' . PHP_EOL . PHP_EOL;

echo '--- TABLES ---' . PHP_EOL;
foreach (['universities', 'departments', 'courses', 'exams', 'topics'] as $table) {
    printf("  %-14s exists=%s  rows=%d\n", $table, Schema::hasTable($table) ? 'yes' : 'NO', DB::table($table)->count());
}

echo PHP_EOL . '--- ROW COUNTS (idempotency) ---' . PHP_EOL;
printf(
    "  universities=%d departments=%d courses=%d exams=%d topics=%d\n",
    University::count(),
    App\Models\Department::count(),
    App\Models\Course::count(),
    App\Models\Exam::count(),
    App\Models\Topic::count(),
);

echo PHP_EOL . '--- FOREIGN KEYS ---' . PHP_EOL;
foreach (['departments', 'courses', 'exams', 'topics'] as $table) {
    $fks = DB::select("
        SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ", [$table]);

    foreach ($fks as $fk) {
        $rule = DB::selectOne("
            SELECT DELETE_RULE
            FROM information_schema.REFERENTIAL_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ?
              AND REFERENCED_TABLE_NAME = ?
        ", [$table, $fk->REFERENCED_TABLE_NAME]);

        printf(
            "  %-12s %-14s -> %s.%s  ON DELETE %s\n",
            $table,
            $fk->COLUMN_NAME,
            $fk->REFERENCED_TABLE_NAME,
            $fk->REFERENCED_COLUMN_NAME,
            $rule->DELETE_RULE ?? '?'
        );
    }
}

echo PHP_EOL . '--- INDEXES ---' . PHP_EOL;
foreach (['universities', 'departments', 'courses', 'exams', 'topics'] as $table) {
    foreach (DB::select("SHOW INDEX FROM `{$table}`") as $index) {
        printf(
            "  %-14s %-28s unique=%s col=%s seq=%d\n",
            $table,
            $index->Key_name,
            $index->Non_unique ? 'no' : 'YES',
            $index->Column_name,
            $index->Seq_in_index
        );
    }
}

echo PHP_EOL . '--- REQUIRED FULL CHAIN (§14) ---' . PHP_EOL;
$topic = App\Models\Topic::where('name', 'Polymorphism')->first();

if ($topic === null) {
    echo '  FAIL: Polymorphism topic not found' . PHP_EOL;
    exit(1);
}

$university = $topic->exam->course->department->university;
$department = $topic->exam->course->department;
$course = $topic->exam->course;
$exam = $topic->exam;

$chain = [
    ['University', $university->name . ' (' . $university->code . ')'],
    ['Department', $department->name . ' (' . $department->code . ')'],
    ['Course', $course->name . ' (' . $course->code . ')'],
    ['Exam', $exam->title . ' (' . $exam->type . ')'],
    ['Topic', $topic->name . ' (order ' . $topic->order . ')'],
];

foreach ($chain as $i => [$label, $value]) {
    printf("  %s%s\n", str_repeat('    ', $i), $label . ': ' . $value);
}

echo PHP_EOL . '--- MIDTERM TOPIC ORDER ---' . PHP_EOL;
$midterm = App\Models\Exam::where('type', 'midterm')
    ->whereHas('course', fn($q) => $q->where('code', 'CSE221'))
    ->first();

foreach ($midterm->topics()->orderBy('order')->get() as $t) {
    printf("  %d. %s\n", $t->order, $t->name);
}

echo PHP_EOL . '--- FINAL TOPIC ORDER ---' . PHP_EOL;
$final = App\Models\Exam::where('type', 'final')
    ->whereHas('course', fn($q) => $q->where('code', 'CSE221'))
    ->first();

foreach ($final->topics()->orderBy('order')->get() as $t) {
    printf("  %d. %s\n", $t->order, $t->name);
}

echo PHP_EOL . '--- ALL COURSES UNDER CSE ---' . PHP_EOL;
foreach (App\Models\Course::orderBy('code')->get() as $c) {
    printf(
        "  %-8s %-30s semester=%-3s credit=%s\n",
        $c->code,
        $c->name,
        $c->semester ?? '-',
        $c->credit ?? '-'
    );
}

echo PHP_EOL . 'OK' . PHP_EOL;
