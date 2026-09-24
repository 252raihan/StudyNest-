<?php

namespace App\Http\Requests\Admin;

use App\Models\University;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

/**
 * Guards the DELETE request for a university.
 *
 * Deletion of a university fans out through four cascading foreign keys, so the
 * admin must type the exact university name. That check is enforced here, on
 * the server — it is never trusted to JavaScript or a hidden form field.
 */
class DestroyUniversityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'confirmation_name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirmation_name.required' => 'Please type the university name to confirm deletion.',
        ];
    }

    /**
     * Verify the typed name matches the university being deleted.
     *
     * Comparison is case-insensitive and whitespace-tolerant so a correct
     * confirmation is not rejected over trivial formatting, while a genuinely
     * wrong name still fails.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $typed = $this->normalise($this->string('confirmation_name')->value());
            $expected = $this->normalise($this->university()->name);

            if ($typed !== $expected) {
                $validator->errors()->add(
                    'confirmation_name',
                    'The name you typed does not match "'.$this->university()->name.'". Deletion cancelled.'
                );
            }
        });
    }

    /**
     * The university targeted for deletion, resolved from the route binding.
     */
    public function university(): University
    {
        return $this->route('university');
    }

    /**
     * Collapse whitespace and normalise case before comparing.
     */
    protected function normalise(string $value): string
    {
        return Str::lower(preg_replace('/\s+/u', ' ', trim($value)) ?? '');
    }
}
