<?php

namespace App\Http\Requests;

use App\Support\PhoneNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class WithdrawSa96RegistrationRequest extends FormRequest
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
            'confirm_withdraw' => ['accepted'],
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
                'phone.required' => 'Please enter the mobile number used to register.',
                'confirm_withdraw.accepted' => 'Please confirm that you want to withdraw consent and delete your data.',
            ];
        }

        return [
            'phone.required' => 'يرجى إدخال رقم الجوال المستخدم في التسجيل.',
            'confirm_withdraw.accepted' => 'يرجى تأكيد رغبتك في سحب الموافقة وحذف بياناتك.',
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
