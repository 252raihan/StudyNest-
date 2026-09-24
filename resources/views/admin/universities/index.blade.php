@extends('layouts.admin')

@section('title', 'Universities')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Universities</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ number_format($universities->total()) }}
                {{ Str::plural('university', $universities->total()) }} in the catalogue.
            </p>
        </div>

        <x-button href="{{ route('admin.universities.create') }}">
            + Add University
        </x-button>
    </div>

    <form method="GET" action="{{ route('admin.universities.index') }}"
          class="mb-4 flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow-sm">
        <div class="min-w-56 flex-1">
            <label for="search" class="block text-sm font-medium text-slate-700">Search</label>
            <input id="search"
                   name="search"
                   type="search"
                   value="{{ $search }}"
                   placeholder="Name or code"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>

        <div class="min-w-40">
            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
            <select id="status"
                    name="status"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="">All</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>
        </div>

        <x-button type="submit" variant="secondary">Filter</x-button>

        @if ($search !== '' || $status)
            <x-button href="{{ route('admin.universities.index') }}" variant="ghost">
                Clear
            </x-button>
        @endif
    </form>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        @if ($universities->isEmpty())
            <div class="px-6 py-12 text-center">
                <p class="text-sm text-slate-500">No universities found.</p>
                <div class="mt-4">
                    <x-button href="{{ route('admin.universities.create') }}">
                        Add the first university
                    </x-button>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th scope="col" class="px-4 py-3">ID</th>
                            <th scope="col" class="px-4 py-3">Name</th>
                            <th scope="col" class="px-4 py-3">Code</th>
                            <th scope="col" class="px-4 py-3">Departments</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3">Created At</th>
                            <th scope="col" class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($universities as $university)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 tabular-nums text-slate-500">{{ $university->id }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    <a href="{{ route('admin.universities.show', $university) }}"
                                       class="hover:text-indigo-600">
                                        {{ $university->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-700">
                                        {{ $university->code }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 tabular-nums text-slate-600">
                                    {{ number_format($university->departments_count) }}
                                </td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$university->status" />
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                                    {{ $university->created_at?->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.universities.show', $university) }}"
                                           class="rounded px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                                            View
                                        </a>
                                        <a href="{{ route('admin.universities.edit', $university) }}"
                                           class="rounded px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50">
                                            Edit
                                        </a>
                                        <a href="{{ route('admin.universities.confirm-delete', $university) }}"
                                           class="rounded px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($universities->hasPages())
        <div class="mt-4">
            {{ $universities->links() }}
        </div>
    @endif
@endsection