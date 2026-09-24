{{--
    Shared university form fields.

    Expects:
      $university  — University model (existing) or null (creating)
      $action      — form submission URL
      $method      — 'POST' or 'PUT'
      $submitLabel — text for the submit button
--}}

@php($university ??= null)

<form method="POST" action="{{ $action }}" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-6 rounded-xl bg-white p-6 shadow-sm">
        <x-form.input name="name"
                      label="Name"
                      :value="old('name', $university?->name)"
                      required
                      maxlength="255"
                      placeholder="Daffodil International University" />

        <x-form.input name="code"
                      label="Code"
                      :value="old('code', $university?->code)"
                      required
                      maxlength="50"
                      placeholder="DIU"
                      help="A short unique identifier, e.g. DIU. Stored in upper case." />

        <x-form.select name="status"
                       label="Status"
                       required
                       help="Inactive universities stay in the database but can be hidden from students later.">
            <option value="1" @selected((string) old('status', $university ? (int) $university->status : 1) === '1')>
                Active
            </option>
            <option value="0" @selected((string) old('status', $university ? (int) $university->status : 1) === '0')>
                Inactive
            </option>
        </x-form.select>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <x-button type="submit">{{ $submitLabel }}</x-button>

        @if ($university)
            <x-button href="{{ route('admin.universities.show', $university) }}" variant="secondary">
                Cancel
            </x-button>
        @else
            <x-button href="{{ route('admin.universities.index') }}" variant="secondary">
                Cancel
            </x-button>
        @endif
    </div>
</form>