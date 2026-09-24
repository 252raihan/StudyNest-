@extends('layouts.app')

@section('title', 'Profile — StudyNest')

@section('content')
    <section class="mx-auto max-w-2xl rounded-2xl bg-white px-6 py-10 shadow-sm sm:px-10">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Your profile</h1>
        <p class="mt-2 text-sm text-slate-500">
            Profile editing is not available yet.
        </p>

        <dl class="mt-8 divide-y divide-slate-100">
            <div class="flex flex-col gap-1 py-4 sm:flex-row sm:items-center sm:justify-between">
                <dt class="text-sm font-medium text-slate-500">Name</dt>
                <dd class="text-slate-900">{{ $user->name }}</dd>
            </div>
            <div class="flex flex-col gap-1 py-4 sm:flex-row sm:items-center sm:justify-between">
                <dt class="text-sm font-medium text-slate-500">Email</dt>
                <dd class="break-all text-slate-900">{{ $user->email }}</dd>
            </div>
            <div class="flex flex-col gap-1 py-4 sm:flex-row sm:items-center sm:justify-between">
                <dt class="text-sm font-medium text-slate-500">Role</dt>
                <dd class="text-slate-900">{{ $user->role }}</dd>
            </div>
        </dl>
    </section>
@endsection