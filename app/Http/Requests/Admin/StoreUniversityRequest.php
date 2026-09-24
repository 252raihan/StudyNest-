<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUniversityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * The `admin` middleware already guards this route; this is a second
     * server-side check so the request is never authorised by UI alone.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Only `name`, `code` and `status` are ever read. `id`, `created_at` and
     * `updated_at` are not validated and cannot be mass assigned.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'code' => ['required', 'string', 'min:2', 'max:50', 'unique:universities,code'],
            'status' => ['required', 'boolean'],
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
            'name.required' => 'Please enter the university name.',
            'name.min' => 'The university name must be at least 2 characters.',
            'name.max' => 'The university name may not be longer than 255 characters.',
            'code.required' => 'Please enter the university code.',
            'code.min' => 'The university code must be at least 2 characters.',
            'code.max' => 'The university code may not be longer than 50 characters.',
            'code.unique' => 'This university code is already in use. Codes must be unique.',
            'status.required' => 'Please choose a status.',
            'status.boolean' => 'The status must be either Active or Inactive.',
        ];
    }

    /**
     * Normalise the submitted code to upper case.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->input('code'))),
            ]);
        }
    }
}
