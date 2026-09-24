@extends('layouts.app')

@section('title', 'Register — StudyNest')

@section('content')
    <div class="mx-auto max-w-md">
        <section class="rounded-2xl bg-white px-6 py-8 shadow-sm sm:px-8">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Create your account</h1>
            <p class="mt-2 text-sm text-slate-500">
                Student accounts only. Administrator access is granted separately.
            </p>

            {{--
                No role field is rendered here, and the server ignores any
                `role` value sent with this request.
            --}}
            <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5" novalidate>
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">
                        Name
                    </label>
                    <input id="name"
                           name="name"
                           type="text"
                           value="{{ old('name') }}"
                           required
                           autofocus
                           autocomplete="name"
                           class="mt-1 block w-full rounded-lg border @error('name') border-red-400 @else border-slate-300 @enderror px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">
                        Email
                    </label>
                    <input id="email"
                           name="email"
                           type="email"
                           value="{{ old('email') }}"
                           required
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
                           autocomplete="new-password"
                           class="mt-1 block w-full rounded-lg border @error('password') border-red-400 @else border-slate-300 @enderror px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-slate-500">
                        At least 8 characters, including letters and numbers.
                    </p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">
                        Confirm password
                    </label>
                    <input id="password_confirmation"
                           name="password_confirmation"
                           type="password"
                           required
                           autocomplete="new-password"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Register
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                Already have an account?
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-700">
                    Login
                </a>
            </p>
        </section>
    </div>
@endsection