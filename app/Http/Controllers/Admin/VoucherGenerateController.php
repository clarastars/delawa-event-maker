<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoucherGenerateRequest;
use App\Models\Event;
use App\Models\Product;
use App\Services\LocalVoucherGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherGenerateController extends Controller
{
    public function __construct(private LocalVoucherGenerator $generator) {}

    public function create(Request $request): View
    {
        return view('admin.vouchers.generate', [
            'events' => Event::query()->with('products')->orderBy('name')->get(),
            'selectedEventId' => $request->integer('event') ?: null,
        ]);
    }

    public function store(VoucherGenerateRequest $request): RedirectResponse
    {
        $event = Event::query()->findOrFail($request->validated('event_id'));
        $product = $request->validated('product_id')
            ? Product::query()->findOrFail($request->validated('product_id'))
            : null;

        $summary = $this->generator->generate(
            event: $event,
            quantity: (int) $request->validated('quantity'),
            balance: (float) $request->validated('balance'),
            product: $product,
            expiryDate: $request->validated('expiry_date'),
            oneTimeRedemption: $request->boolean('one_time_redemption'),
        );

        return redirect()
            ->route('admin.events.show', $event)
            ->with('status', "Generated {$summary['created']} local voucher(s) for {$event->name}.");
    }
}
