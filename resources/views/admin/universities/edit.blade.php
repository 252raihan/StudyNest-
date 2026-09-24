@extends('layouts.admin')

@section('title', 'Edit '.$university->name)

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit University</h1>
        <p class="mt-1 text-sm text-slate-500">
            Updating <span class="font-medium text-slate-700">{{ $university->name }}</span>.
        </p>
    </div>

    <div class="max-w-2xl">
        @include('admin.universities.partials.form', [
            'university' => $university,
            'action' => route('admin.universities.update', $university),
            'method' => 'PUT',
            'submitLabel' => 'Save Changes',
        ])
    </div>
@endsection