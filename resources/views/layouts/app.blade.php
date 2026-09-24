<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased flex flex-col">
    <header class="bg-white shadow-sm">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="text-xl font-bold text-indigo-600">
                StudyNest
            </a>

                    <ul class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm font-medium">
                        <li>
                            <a href="{{ route('home') }}"
                               class="{{ request()->routeIs('home') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600' }}">
                                Home
                            </a>
                        </li>

                        @guest
                            <li>
                                <a href="{{ route('about') }}"
                                   class="{{ request()->routeIs('about') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600' }}">
                                    About
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('login') }}"
                                   class="{{ request()->routeIs('login') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600' }}">
                                    Login
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('register') }}"
                                   class="{{ request()->routeIs('register') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600' }}">
                                    Register
                                </a>
                            </li>
                        @endguest

                        @auth
                            <li>
                                <a href="{{ route('profile') }}"
                                   class="{{ request()->routeIs('profile') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600' }}">
                                    Profile
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('dashboard') }}"
                                   class="{{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600' }}">
                                    Dashboard
                                </a>
                            </li>

                            {{--
                                Hiding this link is a usability convenience only.
                                Server-side authorization is enforced by the `admin`
                                middleware on every /admin/* route.
                            --}}
                            @if (auth()->user()->isAdmin())
                                <li>
                                    <a href="{{ route('admin.dashboard') }}"
                                       class="{{ request()->routeIs('admin.*') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600' }}">
                                        Admin
                                    </a>
                                </li>
                            @endif

                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="cursor-pointer font-medium text-slate-600 hover:text-indigo-600">
                                        Logout
                                    </button>
                                </form>
                            </li>
                        @endauth
                    </ul>
                </nav>
            </header>
            <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-10 sm:px-6 lg:px-8">
                @if (session('status'))
                    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                         role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-6 text-center text-sm text-slate-500 sm:px-6 lg:px-8">
            <p>&copy; {{ date('Y') }} StudyNest</p>
            <p class="mt-1">Find the right resource. Study smarter.</p>
        </div>
    </footer>
</body>
</html>