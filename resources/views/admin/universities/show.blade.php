@extends('layouts.admin')

@section('title', $university->name)

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.universities.index') }}"
           class="text-sm font-medium text-slate-500 hover:text-slate-700">
            &larr; Back to Universities
        </a>

        <div class="mt-3 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ $university->name }}
                </h1>
                <div class="mt-2 flex items-center gap-3">
                    <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-700">
                        {{ $university->code }}
                    </span>
                    <x-status-badge :status="$university->status" />
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-button href="{{ route('admin.universities.edit', $university) }}" variant="secondary">
                    Edit
                </x-button>
                <x-button href="{{ route('admin.universities.confirm-delete', $university) }}" variant="danger">
                    Delete
                </x-button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="text-base font-semibold text-slate-900">Details</h2>

            <dl class="mt-4 divide-y divide-slate-100">
                <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <dt class="text-sm font-medium text-slate-500">University Name</dt>
                    <dd class="text-slate-900">{{ $university->name }}</dd>
                </div>
                <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <dt class="text-sm font-medium text-slate-500">University Code</dt>
                    <dd class="font-mono text-slate-900">{{ $university->code }}</dd>
                </div>
                <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <dt class="text-sm font-medium text-slate-500">Status</dt>
                    <dd><x-status-badge :status="$university->status" /></dd>
                </div>
                <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <dt class="text-sm font-medium text-slate-500">Created At</dt>
                    <dd class="text-slate-900">
                        {{ $university->created_at?->format('d M Y, H:i') ?? '—' }}
                    </dd>
                </div>
                <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <dt class="text-sm font-medium text-slate-500">Updated At</dt>
                    <dd class="text-slate-900">
                        {{ $university->updated_at?->format('d M Y, H:i') ?? '—' }}
                    </dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Related Records</h2>
            <p class="mt-1 text-xs text-slate-500">
                These records are also removed if this university is deleted.
            </p>

            <dl class="mt-4 space-y-3">
                @foreach ($counts as $label => $value)
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-slate-600">{{ ucfirst($label) }}</dt>
                        <dd class="text-lg font-semibold tabular-nums text-slate-900">
                            {{ number_format($value) }}
                        </dd>
                    </div>
                @endforeach
            </dl>

            @if (array_sum($counts) > 0)
                <p class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    Deleting this university also deletes the
                    {{ number_format(array_sum($counts)) }}
                    related {{ Str::plural('record', array_sum($counts)) }} listed above.
                </p>
            @endif
        </div>
    </div>
@endsection