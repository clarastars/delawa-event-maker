<?php

namespace App\Http\Requests;

use App\Models\Event;
use App\Models\Voucher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignVoucherRequest extends FormRequest
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
            'voucher_id' => [
                'required',
                'integer',
                Rule::exists('vouchers', 'id')
                    ->whereNull('contact_id')
                    ->where('status', Voucher::STATUS_ACTIVE)
                    ->where(function ($query): void {
                        $query
                            ->whereNull('event_id')
                            ->orWhereIn('event_id', Event::query()->open()->select('id'));
                    }),
            ],
        ];
    }
}
