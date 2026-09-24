<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PageController extends Controller
{
    /**
     * Display the StudyNest welcome page.
     */
    public function home(): View
    {
        return view('pages.home');
    }

    /**
     * Display the StudyNest about page.
     */
    public function about(): View
    {
        return view('pages.about');
    }

    /**
     * Temporary authentication test page for signed-in users.
     */
    public function dashboard(Request $request): View
    {
        return view('pages.dashboard', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the authenticated user's read-only profile.
     */
    public function profile(Request $request): View
    {
        return view('pages.profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Simple health check endpoint confirming the application is running.
     */
    public function health(): Response
    {
        return response('StudyNest is running.', Response::HTTP_OK)
            ->header('Content-Type', 'text/plain');
    }
}
