<x-admin.layout title="SA96 Registrations">
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black">National Day 96</h1>
                <p class="mt-2 text-sm text-slate-500">
                    Visitor registrations from <span dir="ltr">/sa96</span>.
                    <span class="ms-1 font-semibold text-emerald-700">{{ $activeCount }} active</span>
                    ·
                    <span class="font-semibold text-slate-500">{{ $withdrawnCount }} withdrawn</span>
                </p>
            </div>
            <a
                href="{{ route('admin.sa96.export', request()->only(['search', 'status'])) }}"
                class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50"
            >
                Export CSV
            </a>
        </div>

        <form method="GET" action="{{ route('admin.sa96.index') }}" class="mb-6">
            <div class="flex flex-col gap-3 sm:flex-row">
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search by phone or birth year..."
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                >
                <select
                    name="status"
                    class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                >
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="withdrawn" @selected($status === 'withdrawn')>Withdrawn</option>
                    <option value="all" @selected($status === 'all')>All</option>
                </select>
                <button class="shrink-0 rounded-2xl bg-[#7D4651] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                    Search
                </button>
                @if ($search !== '' || $status !== 'active')
                    <a href="{{ route('admin.sa96.index') }}" class="shrink-0 rounded-2xl border border-slate-200 bg-white px-6 py-3 text-center text-sm font-bold text-slate-700">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200">
            <table class="w-full min-w-[820px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Birth year</th>
                        <th class="px-4 py-3">Locale</th>
                        <th class="px-4 py-3">Marketing</th>
                        <th class="px-4 py-3">Consented</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($registrations as $registration)
                        <tr class="{{ $registration->isWithdrawn() ? 'bg-slate-50/70' : '' }}">
                            <td class="px-4 py-4 font-semibold text-slate-950">{{ $registration->name ?: '—' }}</td>
                            <td class="px-4 py-4 text-slate-600" dir="ltr">{{ $registration->phone ?: '—' }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $registration->birth_year ?: '—' }}</td>
                            <td class="px-4 py-4 text-slate-600 uppercase">{{ $registration->locale }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $registration->consent_marketing ? 'Yes' : 'No' }}</td>
                            <td class="px-4 py-4 text-slate-600">
                                {{ $registration->consented_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?: '—' }}
                            </td>
                            <td class="px-4 py-4">
                                @if ($registration->isWithdrawn())
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">Withdrawn</span>
                                @else
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-100">Active</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">No registrations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $registrations->links() }}
        </div>
    </section>
</x-admin.layout>
