<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class VoucherGenerateRequest extends FormRequest
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
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'balance' => ['required', 'numeric', 'min:0'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:today'],
            'one_time_redemption' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $productId = $this->integer('product_id') ?: null;
            $eventId = $this->integer('event_id') ?: null;

            if ($productId === null || $eventId === null) {
                return;
            }

            $productBelongsToEvent = Product::query()
                ->whereKey($productId)
                ->where('event_id', $eventId)
                ->exists();

            if (! $productBelongsToEvent) {
                $validator->errors()->add('product_id', 'The selected product does not belong to the selected event.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'one_time_redemption' => $this->boolean('one_time_redemption'),
            'product_id' => $this->filled('product_id') ? $this->input('product_id') : null,
        ]);
    }
}
