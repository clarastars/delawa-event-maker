<x-admin.layout title="Vouchers">
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black">Vouchers</h1>
                <p class="mt-2 text-sm text-slate-500">Create vouchers, then upload contacts to assign available voucher IDs.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.vouchers.upload.create') }}" class="rounded-2xl border border-[#7D4651] bg-white px-5 py-3 text-sm font-bold text-[#4E2E36] hover:bg-[#7D4651]/5">
                    Upload CSV
                </a>
                <a href="{{ route('admin.vouchers.generate.create') }}" class="rounded-2xl border border-[#7D4651] bg-white px-5 py-3 text-sm font-bold text-[#4E2E36] hover:bg-[#7D4651]/5">
                    Generate local
                </a>
                <a href="{{ route('admin.vouchers.create') }}" class="rounded-2xl bg-[#7D4651] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                    Add Voucher
                </a>
                <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                    @csrf
                    <button class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                        Log out
                    </button>
                </form>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.vouchers.index') }}" class="mb-6 flex flex-col gap-3 sm:flex-row">
            <select name="event" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20 sm:max-w-xs">
                <option value="">All events</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" @selected($selectedEventId === $event->id)>{{ $event->name }}</option>
                @endforeach
            </select>
            <button class="shrink-0 rounded-2xl bg-[#7D4651] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                Filter
            </button>
            @if ($selectedEventId)
                <a href="{{ route('admin.vouchers.index') }}" class="shrink-0 rounded-2xl border border-slate-200 bg-white px-6 py-3 text-center text-sm font-bold text-slate-700">
                    Clear
                </a>
            @endif
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Voucher ID</th>
                        <th class="px-4 py-3">Event</th>
                        <th class="px-4 py-3">Dates</th>
                        <th class="px-4 py-3">Balance</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Assigned To</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($vouchers as $voucher)
                        <tr>
                            <td class="px-4 py-4 font-bold text-slate-950">{{ $voucher->voucher_id }}</td>
                            <td class="px-4 py-4 text-slate-600">
                                @if ($voucher->event)
                                    <a href="{{ route('admin.events.show', $voucher->event) }}" class="font-semibold text-[#4E2E36] hover:underline">{{ $voucher->event->name }}</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                {{ $voucher->creation_date?->format('Y-m-d') }}
                                <span class="text-slate-300">to</span>
                                {{ $voucher->expiry_date?->format('Y-m-d') ?? 'No expiry' }}
                            </td>
                            <td class="px-4 py-4 font-semibold">{{ number_format((float) $voucher->balance, 2) }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ ucfirst($voucher->status) }}</span>
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                @if ($voucher->contact)
                                    {{ $voucher->contact->name ?: 'Unnamed' }}
                                    <span class="block text-xs text-slate-400">{{ $voucher->contact->phone }}</span>
                                @else
                                    <span class="text-slate-400">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="rounded-full bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700">Edit</a>
                                    <form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-full bg-red-50 px-3 py-2 text-xs font-bold text-red-700">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">No vouchers yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $vouchers->links() }}
        </div>
    </section>
</x-admin.layout>
