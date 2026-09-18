<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'maps_link' => ['nullable', 'url', 'max:2000'],
            'maps_link_label' => ['nullable', 'string', 'max:255'],
            'terms' => ['nullable', 'string', 'max:10000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => [
                'nullable',
                'date',
                Rule::when($this->filled('starts_at'), ['after:starts_at']),
            ],
        ];
    }
}
