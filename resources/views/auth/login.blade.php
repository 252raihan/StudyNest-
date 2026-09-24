@extends('layouts.app')

@section('title', 'Login — StudyNest')

@section('content')
    <div class="mx-auto max-w-md">
        <section class="rounded-2xl bg-white px-6 py-8 shadow-sm sm:px-8">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Login</h1>
            <p class="mt-2 text-sm text-slate-500">
                Welcome back. Sign in to continue to StudyNest.
            </p>

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5" novalidate>
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">
                        Email
                    </label>
                    <input id="email"
                           name="email"
                           type="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           autocomplete="username"
                           class="mt-1 block w-full rounded-lg border @error('email') border-red-400 @else border-slate-300 @enderror px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">
                        Password
                    </label>
                    <input id="password"
                           name="password"
                           type="password"
                           required
                           autocomplete="current-password"
                           class="mt-1 block w-full rounded-lg border @error('password') border-red-400 @else border-slate-300 @enderror px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1"
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Remember me
                </label>

                <button type="submit"
                        class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Login
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                New to StudyNest?
                <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-700">
                    Create a student account
                </a>
            </p>
        </section>
    </div>
@endsection