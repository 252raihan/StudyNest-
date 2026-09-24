<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Topic;
use App\Models\University;
use Illuminate\Database\Seeder;

/**
 * Development / testing seed data for the academic hierarchy:
 *
 *     University > Department > Course > Exam > Topic
 *
 * This seeder is idempotent — every record is resolved with updateOrCreate on
 * its natural key, so running `php artisan db:seed` repeatedly will not create
 * duplicates. It never truncates or drops data.
 */
class AcademicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $university = University::query()->updateOrCreate(
            ['code' => 'DIU'],
            [
                'name' => 'Daffodil International University',
                'status' => true,
            ]
        );

        $department = Department::query()->updateOrCreate(
            [
                'university_id' => $university->id,
                'code' => 'CSE',
            ],
            [
                'name' => 'Computer Science and Engineering',
                'status' => true,
            ]
        );

        $oop = $this->seedCourse($department, [
            'code' => 'CSE221',
            'name' => 'Object Oriented Programming',
            'semester' => 3,
            'credit' => 3.0,
        ]);

        $this->seedCourse($department, [
            'code' => 'CSE123',
            'name' => 'Data Structures',
            'semester' => 2,
            'credit' => 3.0,
        ]);

        $this->seedCourse($department, [
            'code' => 'MAT102',
            'name' => 'Mathematics',
            'semester' => 1,
            'credit' => 3.0,
        ]);

        $midterm = $this->seedExam($oop, Exam::TYPE_MIDTERM, [
            'Class & Object' => 'Objects, classes, attributes and methods.',
            'Constructor' => 'Constructors, default arguments and overloading.',
            'Inheritance' => 'Base and derived classes, single and multilevel inheritance.',
            'Polymorphism' => 'Method overriding and dynamic dispatch.',
        ]);

        $this->seedExam($oop, Exam::TYPE_FINAL, [
            'Exception Handling' => 'try, catch, finally and custom exceptions.',
            'Collections' => 'Lists, maps and sets.',
            'File Handling' => 'Reading from and writing to files.',
        ]);

        $this->command?->info(sprintf(
            'Academic data ready: %s > %s > CSE221 > %s (%d topics)',
            $university->code,
            $department->code,
            $midterm->type,
            $midterm->topics()->count()
        ));
    }

    /**
     * Create or update a course within a department.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function seedCourse(Department $department, array $attributes): Course
    {
        return Course::query()->updateOrCreate(
            [
                'department_id' => $department->id,
                'code' => $attributes['code'],
            ],
            [
                'name' => $attributes['name'],
                'semester' => $attributes['semester'],
                'credit' => $attributes['credit'],
                'status' => true,
            ]
        );
    }

    /**
     * Create or update an exam and replace its ordered topic list.
     *
     * @param  array<string, string>  $topics  Topic name => description.
     */
    protected function seedExam(Course $course, string $type, array $topics): Exam
    {
        $exam = Exam::query()->updateOrCreate(
            [
                'course_id' => $course->id,
                'type' => $type,
            ],
            [
                'title' => ucfirst($type).' Examination',
            ]
        );

        $order = 1;
        foreach ($topics as $name => $description) {
            Topic::query()->updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'order' => $order,
                ],
                [
                    'name' => $name,
                    'description' => $description,
                ]
            );

            $order++;
        }

        return $exam;
    }
}
