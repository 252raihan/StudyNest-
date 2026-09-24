@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard</h1>
        <p class="mt-1 text-sm text-slate-500">
            Overview of the StudyNest academic catalogue.
        </p>
    </div>

    @php
        $cards = [
            ['label' => 'Universities', 'value' => $counts['universities'], 'route' => 'admin.universities.index'],
            ['label' => 'Departments', 'value' => $counts['departments'], 'route' => null],
            ['label' => 'Courses', 'value' => $counts['courses'], 'route' => null],
            ['label' => 'Exams', 'value' => $counts['exams'], 'route' => null],
            ['label' => 'Topics', 'value' => $counts['topics'], 'route' => null],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($cards as $card)
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                <p class="mt-2 text-3xl font-bold tabular-nums text-slate-900">
                    {{ number_format($card['value']) }}
                </p>

                @if ($card['route'])
                    <a href="{{ route($card['route']) }}"
                       class="mt-4 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-700">
                        Manage &rarr;
                    </a>
                @else
                    <p class="mt-4 text-xs text-slate-400">Coming in the next milestone</p>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-8 rounded-xl bg-white p-5 shadow-sm">
        <h2 class="text-base font-semibold text-slate-900">Getting started</h2>
        <p class="mt-1 text-sm text-slate-500">
            University management is available now. Departments, courses, exams and
            topics are managed in upcoming milestones.
        </p>
        <div class="mt-4">
            <x-button href="{{ route('admin.universities.create') }}">
                Add University
            </x-button>
        </div>
    </div>
@endsection