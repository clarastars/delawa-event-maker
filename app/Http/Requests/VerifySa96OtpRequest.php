<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifySa96OtpRequest extends FormRequest
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
            'otp' => ['required', 'digits:'.config('services.authentica.otp_digits', 4)],
            'lang' => ['nullable', 'in:ar,en'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        if ($this->locale() === 'en') {
            return [
                'otp.required' => 'Please enter the verification code.',
                'otp.digits' => 'Please enter a valid verification code.',
            ];
        }

        return [
            'otp.required' => 'يرجى إدخال رمز التحقق.',
            'otp.digits' => 'يرجى إدخال رمز تحقق صحيح.',
        ];
    }

    public function locale(): string
    {
        return $this->query('lang') === 'en' || $this->input('lang') === 'en' ? 'en' : 'ar';
    }
}
