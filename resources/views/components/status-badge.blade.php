{{--
    Status badge. Renders "Active" / "Inactive" from a boolean.

    Usage: <x-status-badge :status="$university->status" />
--}}
@props(['status' => false])

@php($active = (bool) $status)

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium '
        .($active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'),
]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $active ? 'bg-emerald-500' : 'bg-slate-400' }}"
          aria-hidden="true"></span>
    {{ $active ? 'Active' : 'Inactive' }}
</span>