<x-admin.layout title="Contact">
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.contacts.index') }}" class="text-sm font-semibold text-[#4E2E36] hover:underline">&larr; Back to contacts</a>
                <h1 class="mt-2 text-3xl font-black">{{ $contact->name ?: 'Unnamed contact' }}</h1>
                <p class="mt-2 text-sm text-slate-500">View details and assign a voucher.</p>
            </div>
            <form
                method="POST"
                action="{{ route('admin.contacts.destroy', $contact) }}"
                onsubmit="return confirm('Delete this contact? Any assigned voucher will be unassigned.')"
            >
                @csrf
                @method('DELETE')
                <button class="rounded-2xl bg-red-50 px-5 py-3 text-sm font-bold text-red-700 ring-1 ring-red-200 hover:bg-red-100">
                    Delete contact
                </button>
            </form>
        </div>

        <div class="mb-8 grid gap-6 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Contact details</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="font-semibold text-slate-500">Name</dt>
                        <dd class="mt-1 text-lg font-bold text-slate-950">{{ $contact->name ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Phone</dt>
                        <dd class="mt-1">
                            <form method="POST" action="{{ route('admin.contacts.update', $contact) }}" class="space-y-3">
                                @csrf
                                @method('PUT')
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <input
                                        type="text"
                                        name="phone"
                                        value="{{ old('phone', $contact->phone) }}"
                                        required
                                        dir="ltr"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-950 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                                    >
                                    <button class="shrink-0 rounded-2xl bg-[#7D4651] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                                        Save phone
                                    </button>
                                </div>
                                @error('phone')
                                    <p class="text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </form>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Email</dt>
                        <dd class="mt-1 text-slate-950">{{ $contact->email ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Added</dt>
                        <dd class="mt-1 text-slate-950">{{ $contact->created_at?->format('Y-m-d H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Activated</dt>
                        <dd class="mt-1">
                            @if ($contact->isActivated())
                                <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-800">
                                    {{ $contact->activated_at->format('Y-m-d H:i') }}
                                </span>
                            @else
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">Not yet</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200 p-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Assigned vouchers ({{ $contact->vouchers->count() }})</h2>

                @if ($contact->vouchers->isEmpty())
                    <p class="mt-4 text-sm text-amber-800">No vouchers assigned yet.</p>
                @else
                    <ul class="mt-4 space-y-4">
                        @foreach ($contact->vouchers as $voucher)
                            <li class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xl font-black text-[#4E2E36]">{{ $voucher->voucher_id }}</p>
                                        <p class="mt-1 text-sm text-slate-600">
                                            {{ $voucher->event?->name ?? 'No event' }}
                                            &middot; Card value <span class="font-bold">{{ number_format((float) $voucher->balance, 2) }}</span>
                                            &middot; Remaining
                                            @if ($voucher->remaining_balance !== null)
                                                <span class="font-bold">{{ number_format((float) $voucher->remaining_balance, 2) }}</span>
                                            @else
                                                <span class="text-xs text-slate-400">Not synced</span>
                                            @endif
                                            &middot; <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold">{{ ucfirst($voucher->status) }}</span>
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            Valid: {{ $voucher->creation_date?->format('Y-m-d') }}
                                            @if ($voucher->expiry_date)
                                                &mdash; {{ $voucher->expiry_date->format('Y-m-d') }}
                                            @endif
                                        </p>
                                    </div>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.contacts.unassign-voucher', [$contact, $voucher]) }}"
                                        onsubmit="return confirm('Unassign voucher {{ $voucher->voucher_id }} from this contact?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-full bg-red-50 px-3 py-2 text-xs font-bold text-red-700 ring-1 ring-red-200 hover:bg-red-100">
                                            Unassign
                                        </button>
                                    </form>
                                </div>
                                <svg data-voucher-barcode="{{ $voucher->voucher_id }}" class="mx-auto mt-4 h-12 w-full max-w-[14rem]" aria-hidden="true"></svg>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-6 border-t border-slate-200 pt-6">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Assign another voucher</h3>

                    @if ($availableVouchers->isNotEmpty())
                        <form method="POST" action="{{ route('admin.contacts.assign-voucher', $contact) }}" class="mt-4 space-y-4">
                            @csrf
                            <div>
                                <label for="voucher_id" class="mb-2 block text-sm font-semibold text-slate-700">Select voucher</label>
                                <select
                                    id="voucher_id"
                                    name="voucher_id"
                                    required
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                                >
                                    <option value="">Choose a voucher...</option>
                                    @foreach ($availableVouchers->groupBy(fn ($voucher) => $voucher->event?->name ?? 'No event') as $eventName => $eventVouchers)
                                        <optgroup label="{{ $eventName }}">
                                            @foreach ($eventVouchers as $voucher)
                                                <option value="{{ $voucher->id }}" @selected(old('voucher_id') == $voucher->id)>
                                                    {{ $voucher->voucher_id }} — {{ number_format((float) $voucher->balance, 2) }} (expires {{ $voucher->expiry_date?->format('Y-m-d') ?? 'n/a' }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('voucher_id')
                                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="rounded-2xl bg-[#7D4651] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                                Assign voucher
                            </button>
                        </form>
                    @else
                        <p class="mt-4 text-sm text-slate-500">
                            No unassigned active vouchers available.
                            <a href="{{ route('admin.vouchers.upload.create') }}" class="font-semibold text-[#4E2E36] underline">Upload vouchers</a> first.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        @if ($contact->vouchers->isNotEmpty())
            @push('vite')
                @vite(['resources/js/event-vouchers.js'])
            @endpush
        @endif
    </section>
</x-admin.layout>
