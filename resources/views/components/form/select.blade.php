{{--
    A form select with label and inline validation error.

    Usage:
        <x-form.select name="status" label="Status" :value="old('status')">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </x-form.select>
--}}
@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'help' => null,
])

@php($hasError = $errors->has($name))

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">
            {{ $label }}
            @if ($required)
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <select id="{{ $name }}"
            name="{{ $name }}"
            @if ($required) required @endif
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
            {{ $attributes->merge([
                'class' => 'mt-1 block w-full rounded-lg border px-3 py-2 text-slate-900 shadow-sm '
                    .'focus:outline-none focus:ring-1 '
                    .($hasError
                        ? 'border-red-400 focus:border-red-500 focus:ring-red-500'
                        : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500'),
            ]) }}>
        {{ $slot }}
    </select>

    @if ($help)
        <p class="mt-1 text-xs text-slate-500">{{ $help }}</p>
    @endif

    @error($name)
        <p id="{{ $name }}-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>