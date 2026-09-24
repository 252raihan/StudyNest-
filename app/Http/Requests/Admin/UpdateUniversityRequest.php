<?php

namespace App\Http\Requests\Admin;

use App\Models\University;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUniversityRequest extends FormRequest
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
     * The unique rule ignores the university being edited, so it keeps its own
     * code while another record still cannot reuse it.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'code' => [
                'required',
                'string',
                'min:2',
                'max:50',
                Rule::unique('universities', 'code')->ignore($this->university()->id),
            ],
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

    /**
     * The university being updated, resolved from the route binding.
     */
    protected function university(): University
    {
        return $this->route('university');
    }
}
