<?php

namespace App\Http\Requests;

use App\Models\Contact;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateContactPhoneRequest extends FormRequest
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
            'phone' => ['required', 'string', 'max:30'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $normalized = Contact::normalizePhone($this->string('phone')->toString());

                if ($normalized === '') {
                    $validator->errors()->add('phone', 'Please enter a valid phone number.');

                    return;
                }

                $exists = Contact::query()
                    ->where('phone_normalized', $normalized)
                    ->whereKeyNot($this->route('contact')->id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('phone', 'This phone number is already used by another contact.');
                }
            },
        ];
    }
}
