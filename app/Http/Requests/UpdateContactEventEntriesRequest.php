<?php

namespace App\Http\Requests;

use App\Models\Contact;
use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateContactEventEntriesRequest extends FormRequest
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
            'entries' => ['required', 'integer', 'min:1'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var Contact $contact */
                $contact = $this->route('contact');
                /** @var Event $event */
                $event = $this->route('event');

                if (! $contact->events()->where('events.id', $event->id)->exists()) {
                    $validator->errors()->add('entries', 'This contact is not assigned to that event.');

                    return;
                }

                $claimed = $contact->vouchers()
                    ->where('event_id', $event->id)
                    ->count();

                $entries = $this->integer('entries');

                if ($entries < $claimed) {
                    $validator->errors()->add(
                        'entries',
                        "Entries cannot be less than the {$claimed} voucher(s) already claimed for this event."
                    );
                }
            },
        ];
    }
}
