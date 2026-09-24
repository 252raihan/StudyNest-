<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — StudyNest</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
    <header class="border-b border-slate-800 bg-slate-900">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold text-white">
                    StudyNest <span class="font-medium text-indigo-300">Admin</span>
                </a>
            </div>

            <ul class="flex items-center gap-5 text-sm font-medium">
                <li>
                    <a href="{{ route('home') }}" class="text-slate-300 hover:text-white">
                        View Website
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile') }}" class="text-slate-300 hover:text-white">
                        Profile
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="cursor-pointer font-medium text-slate-300 hover:text-white">
                            Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </header>

    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-8 sm:px-6 lg:flex-row lg:px-8">
        <aside class="w-full shrink-0 lg:w-56">
            <nav class="rounded-xl bg-white p-2 shadow-sm">
                <ul class="space-y-1 text-sm font-medium">
                    @php
                        // Sections that exist but are not built yet. Rendered as
                        // non-interactive labels so we never advertise a page
                        // that does not exist.
                        $navItems = [
                            ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
                            ['label' => 'Universities', 'route' => 'admin.universities.index'],
                            ['label' => 'Departments', 'route' => null],
                            ['label' => 'Courses', 'route' => null],
                            ['label' => 'Exams', 'route' => null],
                            ['label' => 'Topics', 'route' => null],
                        ];
                    @endphp

                    @foreach ($navItems as $item)
                        <li>
                            @if ($item['route'])
                                @php($active = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])))
                                <a href="{{ route($item['route']) }}"
                                   @if ($active) aria-current="page" @endif
                                   class="block rounded-lg px-3 py-2 {{ $active ? 'bg-indigo-50 font-semibold text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    {{ $item['label'] }}
                                </a>
                            @else
                                <span class="block cursor-not-allowed rounded-lg px-3 py-2 text-slate-400"
                                      title="Coming in the next milestone">
                                    {{ $item['label'] }}
                                    <span class="ml-1 text-xs font-normal">(soon)</span>
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>
        </aside>

        <main class="min-w-0 flex-1">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                     role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
                     role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>