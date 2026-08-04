<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignVoucherRequest;
use App\Http\Requests\UpdateContactEventEntriesRequest;
use App\Http\Requests\UpdateContactPhoneRequest;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Voucher;
use App\Services\ContactImporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    public function __construct(private ContactImporter $contactImporter) {}

    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());

        $contacts = Contact::query()
            ->with('vouchers')
            ->withSum('events as entries_count', 'contact_event.entries')
            ->search($search)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contacts.index', [
            'contacts' => $contacts,
            'search' => $search,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $search = trim($request->string('search')->toString());
        $filename = 'contacts-'.now()->format('Y-m-d-His').'.csv';

        return Response::streamDownload(function () use ($search): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['name', 'email', 'phone', 'voucher_ids', 'activated_at', 'card_value', 'remaining_balance']);

            Contact::query()
                ->with('vouchers')
                ->search($search)
                ->latest()
                ->cursor()
                ->each(function (Contact $contact) use ($handle): void {
                    $vouchers = $contact->vouchers;
                    $activated = $contact->isActivated();

                    $remainingBalances = $vouchers->whereNotNull('remaining_balance');

                    fputcsv($handle, [
                        $contact->name ?? '',
                        $contact->email ?? '',
                        $contact->phone,
                        $vouchers->pluck('voucher_id')->implode(' | '),
                        $activated ? $contact->activated_at->format('Y-m-d H:i') : '',
                        $activated && $vouchers->isNotEmpty()
                            ? number_format((float) $vouchers->sum('balance'), 2, '.', '')
                            : '',
                        $activated && $remainingBalances->isNotEmpty()
                            ? number_format((float) $remainingBalances->sum('remaining_balance'), 2, '.', '')
                            : '',
                    ]);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function show(Contact $contact): View
    {
        $contact->load(['vouchers.event', 'events']);

        $availableVouchers = Voucher::query()
            ->with('event')
            ->whereNull('contact_id')
            ->where('status', Voucher::STATUS_ACTIVE)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('event_id')
                    ->orWhereHas('event', fn (Builder $eventQuery) => $eventQuery->open());
            })
            ->orderBy('voucher_id')
            ->get();

        return view('admin.contacts.show', [
            'contact' => $contact,
            'availableVouchers' => $availableVouchers,
        ]);
    }

    public function update(UpdateContactPhoneRequest $request, Contact $contact): RedirectResponse
    {
        $phone = trim($request->validated('phone'));

        $contact->update([
            'phone' => $phone,
            'phone_normalized' => Contact::normalizePhone($phone),
        ]);

        return redirect()
            ->route('admin.contacts.show', $contact)
            ->with('status', 'Phone number updated.');
    }

    public function updateEntries(UpdateContactEventEntriesRequest $request, Contact $contact, Event $event): RedirectResponse
    {
        $contact->events()->updateExistingPivot($event->id, [
            'entries' => (int) $request->validated('entries'),
        ]);

        return redirect()
            ->route('admin.contacts.show', $contact)
            ->with('status', "Entries for {$event->name} updated.");
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()
            ->route('admin.contacts.index')
            ->with('status', 'Contact deleted.');
    }

    public function assignVoucher(AssignVoucherRequest $request, Contact $contact): RedirectResponse
    {
        $result = $this->contactImporter->assignVoucher(
            $contact,
            (int) $request->validated('voucher_id')
        );

        if ($result['assigned']) {
            return redirect()
                ->route('admin.contacts.show', $contact)
                ->with('status', "Voucher {$result['voucher']?->voucher_id} assigned successfully.");
        }

        return redirect()
            ->route('admin.contacts.show', $contact)
            ->withErrors(['voucher_id' => 'The selected voucher is not available.']);
    }

    public function unassignVoucher(Contact $contact, Voucher $voucher): RedirectResponse
    {
        if ($voucher->contact_id !== $contact->id) {
            return redirect()
                ->route('admin.contacts.show', $contact)
                ->withErrors(['voucher_id' => 'This voucher is not assigned to this contact.']);
        }

        $voucher->update(['contact_id' => null]);

        return redirect()
            ->route('admin.contacts.show', $contact)
            ->with('status', "Voucher {$voucher->voucher_id} unassigned.");
    }
}
