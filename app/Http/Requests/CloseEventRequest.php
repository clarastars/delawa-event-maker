<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'closure_observations' => ['nullable', 'string', 'max:5000'],
            'closure_lessons_learned' => ['nullable', 'string', 'max:5000'],
            'closure_recommendations' => ['nullable', 'string', 'max:5000'],
            'confirmed' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirmed.accepted' => 'Please confirm that you are ready to close this event and disable its invite link.',
        ];
    }
}
