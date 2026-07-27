<?php

namespace App\Http\Requests;

use App\Support\PhoneNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SendAcceptOtpRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'lang' => ['nullable', 'in:ar,en'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $e164 = PhoneNumber::toE164($this->string('phone')->toString());

            if ($e164 === null || ! PhoneNumber::isE164($e164)) {
                $validator->errors()->add('phone', __('Please enter a valid international phone number (e.g. +9665XXXXXXXX).'));
            }
        });
    }

    public function phoneE164(): string
    {
        return PhoneNumber::toE164($this->string('phone')->toString()) ?? '';
    }
}
