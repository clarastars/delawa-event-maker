<x-admin.layout title="Upload Vouchers">
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black">Upload Vouchers</h1>
                <p class="mt-2 text-sm text-slate-500">Import vouchers from a tab-separated or comma-separated file. Existing entry IDs will be updated.</p>
            </div>
            <a href="{{ route('admin.vouchers.index') }}" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Back to vouchers</a>
        </div>

        <div class="grid gap-8 lg:grid-cols-2">
            <form method="POST" action="{{ route('admin.vouchers.upload.store') }}" enctype="multipart/form-data" class="space-y-6" x-data="{ eventId: '{{ old('event_id', $selectedEventId ?? '') }}' }">
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
                            @if($event->products->isNotEmpty())
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

                <div>
                    <label for="vouchers" class="block text-sm font-semibold text-slate-700">Voucher file (CSV / TSV)</label>
                    <input id="vouchers" name="vouchers" type="file" accept=".csv,.txt,text/csv,text/plain" class="mt-2 w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm" required>
                    @error('vouchers')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-3">
                    <button class="rounded-2xl bg-[#7D4651] px-6 py-3 font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                        Upload vouchers
                    </button>
                    <a href="{{ route('admin.vouchers.upload.sample') }}" class="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 hover:border-[#7D4651] hover:text-[#4E2E36]">
                        Download sample file
                    </a>
                </div>
            </form>

            <div class="space-y-6">
                <div class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Expected columns</h2>
                    <ul class="mt-3 space-y-1 text-sm text-slate-700">
                        @foreach ($expectedHeaders as $header)
                            <li><code class="rounded bg-white px-2 py-0.5 text-[#4E2E36]">{{ $header }}</code></li>
                        @endforeach
                    </ul>
                </div>

                <div class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Status values</h2>
                    <ul class="mt-3 space-y-1 text-sm text-slate-700">
                        <li><strong>2</strong> — Active</li>
                        <li><strong>0 / 1</strong> — Inactive</li>
                        <li><strong>3</strong> — Redeemed</li>
                        <li><strong>4</strong> — Expired</li>
                    </ul>
                    <p class="mt-3 text-sm text-slate-500">OneTimeRedemption: <strong>1</strong> = yes, <strong>0</strong> = no. currencyCode is accepted but not stored.</p>
                </div>

                <div class="rounded-2xl bg-slate-950 p-5">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-white/70">Sample format</h2>
                    <pre class="mt-3 overflow-x-auto text-xs leading-6 text-emerald-300" dir="ltr">{{ $sampleCsv }}</pre>
                </div>
            </div>
        </div>
    </section>
</x-admin.layout>
