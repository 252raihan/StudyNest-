<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DestroyUniversityRequest;
use App\Http\Requests\Admin\StoreUniversityRequest;
use App\Http\Requests\Admin\UpdateUniversityRequest;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Topic;
use App\Models\University;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    /**
     * How many universities to show per page.
     */
    protected const PER_PAGE = 15;

    /**
     * Display a paginated list of universities.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $universities = University::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%');
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('status', true))
            ->when($status === 'inactive', fn ($query) => $query->where('status', false))
            ->withCount('departments')
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.universities.index', [
            'universities' => $universities,
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * Show the form for creating a university.
     */
    public function create(): View
    {
        return view('admin.universities.create');
    }

    /**
     * Store a newly created university.
     */
    public function store(StoreUniversityRequest $request): RedirectResponse
    {
        $university = University::create($request->safe()->only('name', 'code', 'status'));

        return redirect()
            ->route('admin.universities.show', $university)
            ->with('status', 'University created successfully.');
    }

    /**
     * Display a university and its related record counts.
     */
    public function show(University $university): View
    {
        return view('admin.universities.show', [
            'university' => $university,
            'counts' => $this->relationCounts($university),
        ]);
    }

    /**
     * Show the form for editing a university.
     */
    public function edit(University $university): View
    {
        return view('admin.universities.edit', [
            'university' => $university,
        ]);
    }

    /**
     * Update a university.
     */
    public function update(UpdateUniversityRequest $request, University $university): RedirectResponse
    {
        $university->update($request->safe()->only('name', 'code', 'status'));

        return redirect()
            ->route('admin.universities.show', $university)
            ->with('status', 'University updated successfully.');
    }

    /**
     * Show the delete confirmation screen.
     *
     * Nothing is deleted here — this only presents the impact of a delete so
     * the admin can decide. Deletion happens in destroy() behind a DELETE
     * request with typed-name confirmation.
     */
    public function confirmDelete(University $university): View
    {
        return view('admin.universities.confirm-delete', [
            'university' => $university,
            'counts' => $this->relationCounts($university),
        ]);
    }

    /**
     * Delete a university and everything beneath it.
     *
     * The cascading foreign keys remove its departments, courses, exams and
     * topics. The typed-name confirmation is verified in the form request
     * before this method is ever reached.
     */
    public function destroy(DestroyUniversityRequest $request, University $university): RedirectResponse
    {
        $name = $university->name;

        $university->delete();

        return redirect()
            ->route('admin.universities.index')
            ->with('status', 'University deleted successfully: '.$name);
    }

    /**
     * Count every record that a delete would cascade through.
     *
     * Each entry compiles to a single `select count(*)` subquery, so no related
     * records are hydrated into memory.
     *
     * @return array<string, int>
     */
    protected function relationCounts(University $university): array
    {
        $departments = $university->departments()->select('id');

        $courses = Course::query()
            ->whereIn('department_id', $departments)
            ->select('id');

        $exams = Exam::query()
            ->whereIn('course_id', $courses)
            ->select('id');

        return [
            'departments' => $university->departments()->count(),
            'courses' => Course::whereIn('department_id', $departments)->count(),
            'exams' => Exam::whereIn('course_id', $courses)->count(),
            'topics' => Topic::whereIn('exam_id', $exams)->count(),
        ];
    }
}
