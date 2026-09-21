<?php

namespace App\Http\Requests;

use App\Support\PhoneNumber;
use App\Support\Sa96Privacy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSa96RegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $birthYear = strtr(trim((string) $this->input('birth_year', '')), [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'birth_year' => $birthYear,
            'lang' => $this->locale(),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:80', 'regex:/^[\p{L}\p{M}\s\'\-\.]+$/u'],
            'birth_year' => ['required', 'integer', 'digits:4', 'min:'.Sa96Privacy::minBirthYear(), 'max:'.Sa96Privacy::maxBirthYear()],
            'phone' => ['required', 'string', 'max:30'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'string', 'max:255'],
            'lang' => ['nullable', 'in:ar,en'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('phone')) {
                    return;
                }

                $e164 = PhoneNumber::toE164($this->string('phone')->toString());

                if ($e164 === null || ! PhoneNumber::isE164($e164)) {
                    $validator->errors()->add('phone', $this->locale() === 'en'
                        ? 'Please enter a valid mobile number (e.g. 5XXXXXXXX).'
                        : 'يرجى إدخال رقم جوال صحيح (مثال: 5XXXXXXXX).');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        if ($this->locale() === 'en') {
            return [
                'name.required' => 'Please enter your name.',
                'name.regex' => 'Please enter a name using letters only.',
                'birth_year.required' => 'Please enter your year of birth.',
                'birth_year.max' => 'You must be at least 18 years old to register.',
                'birth_year.min' => 'Please enter a valid year of birth.',
                'phone.required' => 'Please enter your mobile number.',
                'consent.accepted' => 'Tick the box to join National Day 96.',
            ];
        }

        return [
            'name.required' => 'يرجى إدخال الاسم.',
            'name.regex' => 'يرجى إدخال الاسم باستخدام الأحرف فقط.',
            'birth_year.required' => 'يرجى إدخال سنة الميلاد.',
            'birth_year.max' => 'يجب أن يكون عمرك 18 سنة فأكثر للتسجيل.',
            'birth_year.min' => 'يرجى إدخال سنة ميلاد صحيحة.',
            'phone.required' => 'يرجى إدخال رقم الجوال.',
            'consent.accepted' => 'علّم المربع عشان نكمّل احتفال اليوم الوطني 96.',
        ];
    }

    public function locale(): string
    {
        return $this->query('lang') === 'en' || $this->input('lang') === 'en' ? 'en' : 'ar';
    }

    public function phoneE164(): string
    {
        return PhoneNumber::toE164($this->string('phone')->toString()) ?? '';
    }
}
