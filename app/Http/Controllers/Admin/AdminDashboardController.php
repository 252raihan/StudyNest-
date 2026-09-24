<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Topic;
use App\Models\University;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with academic record counts.
     */
    public function __invoke(): View
    {
        // Each count() compiles to a single `select count(*)` query at the
        // database level — no models are hydrated.
        return view('admin.dashboard', [
            'counts' => [
                'universities' => University::count(),
                'departments' => Department::count(),
                'courses' => Course::count(),
                'exams' => Exam::count(),
                'topics' => Topic::count(),
            ],
        ]);
    }
}
