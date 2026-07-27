<x-admin.layout title="Events">
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black">Events</h1>
                <p class="mt-2 text-sm text-slate-500">Create an event, upload its coupons and banner, then share its invite link.</p>
            </div>
            <a href="{{ route('admin.events.create') }}" class="rounded-2xl bg-[#7D4651] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                Add Event
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200">
            <table class="w-full min-w-[700px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Event</th>
                        <th class="px-4 py-3">Invite link</th>
                        <th class="px-4 py-3">Coupons</th>
                        <th class="px-4 py-3">Assigned</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Banner</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($events as $event)
                        <tr>
                            <td class="px-4 py-4 font-bold text-slate-950">{{ $event->name }}</td>
                            <td class="px-4 py-4">
                                <a href="{{ route('event.invite', $event) }}" target="_blank" rel="noopener noreferrer" class="font-mono text-xs font-semibold text-[#4E2E36] underline underline-offset-2" dir="ltr">
                                    /e/{{ $event->slug }}
                                </a>
                            </td>
                            <td class="px-4 py-4 font-semibold">{{ $event->vouchers_count }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $event->assigned_vouchers_count }}</td>
                            <td class="px-4 py-4">
                                @if ($event->isClosed())
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Closed</span>
                                @else
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800">Open</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if ($event->banner_path)
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800">Uploaded</span>
                                @else
                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800">Missing</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    @if ($event->isClosed())
                                        <a href="{{ route('admin.events.closure.show', $event) }}" class="rounded-full bg-slate-950 px-3 py-2 text-xs font-bold text-white hover:bg-slate-800">
                                            Closure report
                                        </a>
                                    @else
                                        <a href="{{ route('admin.events.current-report', $event) }}" target="_blank" rel="noopener noreferrer" class="rounded-full bg-white px-3 py-2 text-xs font-bold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">
                                            Current Report
                                        </a>
                                        <a href="{{ route('admin.events.close.create', $event) }}" class="rounded-full bg-slate-950 px-3 py-2 text-xs font-bold text-white hover:bg-slate-800">
                                            Close
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.events.show', $event) }}" class="rounded-full bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-[#7D4651]/10 hover:text-[#4E2E36]">
                                        Manage
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                                No events yet. <a href="{{ route('admin.events.create') }}" class="font-semibold text-[#4E2E36] underline">Create your first event</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $events->links() }}
        </div>
    </section>
</x-admin.layout>
