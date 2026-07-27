<?php

namespace App\Http\Requests;

use App\Models\Voucher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VoucherRequest extends FormRequest
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
        $voucher = $this->route('voucher');

        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'voucher_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vouchers', 'voucher_id')->ignore($voucher),
            ],
            'creation_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:creation_date'],
            'balance' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(Voucher::STATUSES)],
            'one_time_redemption' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'one_time_redemption' => $this->boolean('one_time_redemption'),
        ]);
    }
}
