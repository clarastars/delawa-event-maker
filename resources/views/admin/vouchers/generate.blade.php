<x-admin.layout title="Generate Local Vouchers">
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black">Generate Local Vouchers</h1>
                <p class="mt-2 text-sm text-slate-500">Create unique in-app voucher codes that are not synced to Tsepass.</p>
            </div>
            <a href="{{ route('admin.vouchers.index') }}" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Back to vouchers</a>
        </div>

        <form method="POST" action="{{ route('admin.vouchers.generate.store') }}" class="mx-auto max-w-xl space-y-6" x-data="{ eventId: '{{ old('event_id', $selectedEventId ?? '') }}' }">
            @csrf

            <div>
                <label for="event_id" class="block text-sm font-semibold text-slate-700">Event</label>
                <select id="event_id" name="event_id" x-model="eventId" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required>
                    <option value="">Choose an event...</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}" @selected(old('event_id', $selectedEventId) == $event->id)>{{ $event->name }}</option>
                    @endforeach
                </select>
                @error('event_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="eventId" x-cloak>
                <label for="product_id" class="block text-sm font-semibold text-slate-700">Product (Optional)</label>
                <select id="product_id" name="product_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20">
                    <option value="">No specific product (General Pool)</option>
                    @foreach ($events as $event)
                        @if ($event->products->isNotEmpty())
                            <optgroup label="{{ $event->name }}" x-show="eventId == {{ $event->id }}">
                                @foreach ($event->products as $product)
                                    <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">If selected, vouchers will only be available when users pick this product.</p>
                @error('product_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="quantity" class="block text-sm font-semibold text-slate-700">Quantity</label>
                    <input id="quantity" name="quantity" type="number" min="1" max="500" value="{{ old('quantity', 10) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required>
                    @error('quantity')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="balance" class="block text-sm font-semibold text-slate-700">Face value (SR)</label>
                    <input id="balance" name="balance" type="number" step="0.01" min="0" value="{{ old('balance') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required>
                    @error('balance')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="expiry_date" class="block text-sm font-semibold text-slate-700">Expiry Date (Optional)</label>
                <input id="expiry_date" name="expiry_date" type="date" value="{{ old('expiry_date') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20">
                @error('expiry_date')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="one_time_redemption" value="1" @checked(old('one_time_redemption', true)) class="rounded border-slate-300 text-[#7D4651]">
                One-time redemption
            </label>

            <button class="rounded-2xl bg-[#7D4651] px-6 py-3 font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                Generate vouchers
            </button>
        </form>
    </section>
</x-admin.layout>
