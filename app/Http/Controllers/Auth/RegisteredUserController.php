<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Show the public registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Store a newly registered student.
     *
     * The role is always set to `student` on the server. Any `role` value sent
     * with the request is ignored, so privilege escalation through the public
     * form is impossible.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = new User([
                'name' => $request->string('name')->trim()->value(),
                'email' => $request->string('email')->lower()->trim()->value(),
                'password' => $request->string('password')->value(),
            ]);

            $user->role = User::ROLE_STUDENT;
            $user->save();

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Welcome to StudyNest, '.$user->name.'.');
    }
}
