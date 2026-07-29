<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScanController extends Controller
{
    public function index(): View
    {
        return view('admin.scan.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'voucher_id' => ['required', 'string'],
        ]);

        $voucher = Voucher::with(['contact', 'event'])->where('voucher_id', $request->voucher_id)->first();

        if (! $voucher) {
            return back()->with('scan_error', 'Invalid barcode. Voucher not found.');
        }

        // Use the scope to check if it's redeemable.
        // We can check if it matches the conditions of `scopeRedeemable`.
        $isRedeemable = Voucher::redeemable()->where('id', $voucher->id)->exists();

        if (! $isRedeemable) {
            $reason = 'inactive';

            if ($voucher->status === Voucher::STATUS_REDEEMED) {
                $reason = 'already redeemed on '.($voucher->redeemed_at ? $voucher->redeemed_at->format('M d, Y h:i A') : 'an unknown date');
            } elseif ($voucher->status === Voucher::STATUS_EXPIRED || ($voucher->expiry_date && $voucher->expiry_date->isPast())) {
                $reason = 'expired';
            } elseif ($voucher->status === Voucher::STATUS_INACTIVE) {
                $reason = 'inactive';
            }

            return back()->with('scan_error', "Voucher cannot be used. It is {$reason}.");
        }

        $voucher->update([
            'status' => Voucher::STATUS_REDEEMED,
            'redeemed_at' => now(),
        ]);

        $contactName = $voucher->contact ? $voucher->contact->name : 'Unknown Contact';
        $eventName = $voucher->event ? $voucher->event->name : 'Unknown Event';

        return back()->with('scan_success', "Success! Voucher for {$contactName} at {$eventName} has been marked as used.");
    }
}
