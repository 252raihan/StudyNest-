{{--
    Primary / secondary / danger action button.

    Renders as <a> when `href` is given, otherwise as <button>.

    Usage:
        <x-button href="{{ route('admin.universities.create') }}">Add University</x-button>
        <x-button variant="danger" type="submit">Delete</x-button>
--}}
@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'submit',
])

@php
    $base = 'inline-flex items-center justify-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-semibold '
        .'shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50';

    $variants = [
        'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:ring-slate-400',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus:ring-slate-400 shadow-none',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif