<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoucherRequest;
use App\Models\Event;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $eventId = $request->integer('event') ?: null;

        return view('admin.vouchers.index', [
            'vouchers' => Voucher::query()
                ->with(['contact', 'event'])
                ->when($eventId, fn ($query) => $query->where('event_id', $eventId))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'events' => Event::query()->orderBy('name')->get(),
            'selectedEventId' => $eventId,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.vouchers.create', [
            'voucher' => new Voucher([
                'event_id' => $request->integer('event') ?: null,
                'creation_date' => now()->toDateString(),
                'status' => Voucher::STATUS_ACTIVE,
                'one_time_redemption' => true,
            ]),
            'events' => Event::query()->orderBy('name')->get(),
        ]);
    }

    public function store(VoucherRequest $request): RedirectResponse
    {
        Voucher::create($request->validated());

        return redirect()
            ->route('admin.vouchers.index')
            ->with('status', 'Voucher created.');
    }

    public function edit(Voucher $voucher): View
    {
        return view('admin.vouchers.edit', [
            'voucher' => $voucher,
            'events' => Event::query()->orderBy('name')->get(),
        ]);
    }

    public function update(VoucherRequest $request, Voucher $voucher): RedirectResponse
    {
        $voucher->update($request->validated());

        return redirect()
            ->route('admin.vouchers.index')
            ->with('status', 'Voucher updated.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        $voucher->delete();

        return redirect()
            ->route('admin.vouchers.index')
            ->with('status', 'Voucher deleted.');
    }
}
