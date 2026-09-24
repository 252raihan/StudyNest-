@extends('layouts.app')

@section('title', 'Dashboard — StudyNest')

@section('content')
    <section class="mx-auto max-w-2xl rounded-2xl bg-white px-6 py-10 shadow-sm sm:px-10">
        <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">
            Authentication test page
        </p>
        <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">
            Welcome to StudyNest, {{ $user->name }}
        </h1>
        <p class="mt-3 text-lg text-slate-600">
            Role: {{ $user->role }}
        </p>
        <p class="mt-6 text-sm text-slate-500">
            This is a temporary page that only confirms authentication works.
            The real StudyNest dashboard arrives in a later milestone.
        </p>
    </section>
@endsection