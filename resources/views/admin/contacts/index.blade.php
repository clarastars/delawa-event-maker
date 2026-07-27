<x-admin.layout title="Contacts">
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black">Contacts</h1>
                <p class="mt-2 text-sm text-slate-500">Search invitees and manage voucher assignments.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.contacts.export', request()->only('search')) }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Export CSV
                </a>
                <a href="{{ route('admin.contacts.upload.create') }}" class="rounded-2xl border border-[#7D4651] bg-white px-5 py-3 text-sm font-bold text-[#4E2E36] hover:bg-[#7D4651]/5">
                    Add / Import
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.contacts.index') }}" class="mb-6">
            <div class="flex flex-col gap-3 sm:flex-row">
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search by name, email, or phone..."
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                >
                <button class="shrink-0 rounded-2xl bg-[#7D4651] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                    Search
                </button>
                @if ($search !== '')
                    <a href="{{ route('admin.contacts.index') }}" class="shrink-0 rounded-2xl border border-slate-200 bg-white px-6 py-3 text-center text-sm font-bold text-slate-700">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        <div class="mb-6 flex flex-wrap gap-2 text-xs font-semibold text-slate-500">
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-800 ring-1 ring-emerald-100">Full balance</span>
            <span class="rounded-full bg-amber-50 px-3 py-1 text-amber-800 ring-1 ring-amber-100">Partially used</span>
            <span class="rounded-full bg-red-50 px-3 py-1 text-red-700 ring-1 ring-red-100">Fully used</span>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600 ring-1 ring-slate-200">Not synced</span>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200">
            <table class="w-full min-w-[960px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Vouchers</th>
                        <th class="px-4 py-3">Activated</th>
                        <th class="px-4 py-3">Card value</th>
                        <th class="px-4 py-3">Remaining</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($contacts as $contact)
                        <tr>
                            <td class="px-4 py-4 font-semibold text-slate-950">{{ $contact->name ?: '—' }}</td>
                            <td class="px-4 py-4 text-slate-600" dir="ltr">{{ $contact->phone }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $contact->email ?: '—' }}</td>
                            <td class="px-4 py-4">
                                @if ($contact->vouchers->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($contact->vouchers as $voucher)
                                            <x-admin.voucher-balance-crumb :voucher="$voucher" />
                                        @endforeach
                                    </div>
                                @else
                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if ($contact->isActivated())
                                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-800" title="{{ $contact->activated_at->format('Y-m-d H:i') }}">
                                        {{ $contact->activated_at->format('Y-m-d H:i') }}
                                    </span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">Not yet</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                @if ($contact->vouchers->isNotEmpty())
                                    {{ number_format((float) $contact->vouchers->sum('balance'), 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                @if ($contact->vouchers->whereNotNull('remaining_balance')->isNotEmpty())
                                    {{ number_format((float) $contact->vouchers->whereNotNull('remaining_balance')->sum('remaining_balance'), 2) }}
                                @elseif ($contact->vouchers->isNotEmpty())
                                    <span class="text-xs text-slate-400">Not synced</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right">
                                <a href="{{ route('admin.contacts.show', $contact) }}" class="rounded-full bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-[#7D4651]/10 hover:text-[#4E2E36]">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-slate-500">
                                @if ($search !== '')
                                    No contacts found for "{{ $search }}".
                                @else
                                    No contacts yet. <a href="{{ route('admin.contacts.upload.create') }}" class="font-semibold text-[#4E2E36] underline">Add your first contact</a>.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $contacts->links() }}
        </div>
    </section>
</x-admin.layout>
