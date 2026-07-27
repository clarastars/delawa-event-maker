@php
    $statuses = \App\Models\Voucher::STATUSES;
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="event_id" class="block text-sm font-semibold text-slate-700">Event</label>
        <select id="event_id" name="event_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required>
            <option value="">Choose an event...</option>
            @foreach ($events as $event)
                <option value="{{ $event->id }}" @selected(old('event_id', $voucher->event_id) == $event->id)>{{ $event->name }}</option>
            @endforeach
        </select>
        @error('event_id')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="voucher_id" class="block text-sm font-semibold text-slate-700">Voucher ID</label>
        <input id="voucher_id" name="voucher_id" value="{{ old('voucher_id', $voucher->voucher_id) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required>
        @error('voucher_id')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="balance" class="block text-sm font-semibold text-slate-700">Balance</label>
        <input id="balance" name="balance" type="number" step="0.01" min="0" value="{{ old('balance', $voucher->balance ?? 0) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required>
        @error('balance')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="creation_date" class="block text-sm font-semibold text-slate-700">Creation Date</label>
        <input id="creation_date" name="creation_date" type="date" value="{{ old('creation_date', optional($voucher->creation_date)->format('Y-m-d') ?? now()->toDateString()) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required>
        @error('creation_date')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="expiry_date" class="block text-sm font-semibold text-slate-700">Expiry Date</label>
        <input id="expiry_date" name="expiry_date" type="date" value="{{ old('expiry_date', optional($voucher->expiry_date)->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20">
        @error('expiry_date')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="block text-sm font-semibold text-slate-700">Status</label>
        <select id="status" name="status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $voucher->status) === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <label class="flex items-center gap-3 self-end rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700">
        <input type="checkbox" name="one_time_redemption" value="1" @checked(old('one_time_redemption', $voucher->one_time_redemption ?? true)) class="rounded border-slate-300 text-[#7D4651]">
        One-time redemption
    </label>
</div>
