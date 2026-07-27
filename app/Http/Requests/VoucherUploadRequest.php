<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VoucherUploadRequest extends FormRequest
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
            'vouchers' => ['required', 'file', 'extensions:csv,txt,tsv', 'max:5120'],
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ];
    }
}
