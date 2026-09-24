@extends('layouts.admin')

@section('title', 'Add University')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add University</h1>
        <p class="mt-1 text-sm text-slate-500">
            Create a new university. Fields marked
            <span class="text-red-500">*</span> are required.
        </p>
    </div>

    <div class="max-w-2xl">
        @include('admin.universities.partials.form', [
            'university' => null,
            'action' => route('admin.universities.store'),
            'method' => 'POST',
            'submitLabel' => 'Create University',
        ])
    </div>
@endsection