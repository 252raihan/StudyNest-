@extends('layouts.admin')

@section('title', 'Delete '.$university->name)

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.universities.show', $university) }}"
           class="text-sm font-medium text-slate-500 hover:text-slate-700">
            &larr; Back to {{ $university->name }}
        </a>

        <h1 class="mt-3 text-2xl font-bold tracking-tight text-red-700">
            Delete University
        </h1>
    </div>

    <div class="max-w-2xl space-y-6">
        <div class="rounded-xl border border-red-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-600">You are about to delete:</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $university->name }}</p>
            <span class="mt-2 inline-block rounded bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-700">
                {{ $university->code }}
            </span>

            @php($totalRelated = array_sum($counts))

            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4">
                <p class="text-sm font-semibold text-red-900">
                    This will also affect:
                </p>

                <dl class="mt-3 space-y-2">
                    @foreach ($counts as $label => $value)
                        <div class="flex items-center justify-between text-sm">
                            <dt class="text-red-800">{{ ucfirst($label) }}</dt>
                            <dd class="font-semibold tabular-nums text-red-900">
                                {{ number_format($value) }}
                            </dd>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between border-t border-red-200 pt-2 text-sm">
                        <dt class="font-semibold text-red-900">Total related records</dt>
                        <dd class="font-bold tabular-nums text-red-900">
                            {{ number_format($totalRelated) }}
                        </dd>
                    </div>
                </dl>
            </div>

            <p class="mt-4 text-sm font-semibold text-red-700">
                This action cannot be undone.
            </p>
        </div>

        <form method="POST"
              action="{{ route('admin.universities.destroy', $university) }}"
              class="rounded-xl bg-white p-6 shadow-sm"
              novalidate>
            @csrf
            @method('DELETE')

            <label for="confirmation_name" class="block text-sm font-medium text-slate-700">
                Type the university name to confirm:
                <span class="mt-1 block font-mono text-xs text-slate-500">{{ $university->name }}</span>
            </label>

            <input id="confirmation_name"
                   name="confirmation_name"
                   type="text"
                   value="{{ old('confirmation_name') }}"
                   required
                   autocomplete="off"
                   autofocus
                   placeholder="{{ $university->name }}"
                   @error('confirmation_name') aria-invalid="true" aria-describedby="confirmation_name-error" @enderror
                   class="mt-2 block w-full rounded-lg border px-3 py-2 text-slate-900 shadow-sm focus:outline-none focus:ring-1
                          @error('confirmation_name')
                              border-red-400 focus:border-red-500 focus:ring-red-500
                          @else
                              border-slate-300 focus:border-indigo-500 focus:ring-indigo-500
                          @enderror">

            @error('confirmation_name')
                <p id="confirmation_name-error" class="mt-2 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-red-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Permanently Delete University
                </button>

                <x-button href="{{ route('admin.universities.show', $university) }}" variant="secondary">
                    Cancel
                </x-button>
            </div>
        </form>
    </div>
@endsection