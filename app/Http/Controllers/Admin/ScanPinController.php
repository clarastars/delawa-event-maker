<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScanPinController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user() || $request->session()->get('scanner_pin_verified') === true) {
            return redirect()->route('admin.scan.index');
        }

        return view('admin.scan.pin');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'string'],
        ]);

        $expectedPin = (string) config('scanner.pin');

        if (! hash_equals($expectedPin, $validated['pin'])) {
            return back()
                ->withErrors(['pin' => 'Incorrect PIN. Please try again.'])
                ->onlyInput();
        }

        $request->session()->put('scanner_pin_verified', true);

        return redirect()->route('admin.scan.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('scanner_pin_verified');

        return redirect()->route('admin.scan.pin');
    }
}
