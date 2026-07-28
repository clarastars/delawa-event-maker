<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ContactUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contacts' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'assignment_type' => ['nullable', 'string', 'in:entries,auto_assign'],
            'entries' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
