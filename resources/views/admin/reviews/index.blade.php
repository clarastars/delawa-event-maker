<x-admin.layout title="Reviews">
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black">Reviews</h1>
                <p class="mt-2 text-sm text-slate-500">View feedback and experiences submitted by users.</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200">
            <table class="w-full min-w-[960px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Event</th>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3">Experience</th>
                        <th class="px-4 py-3">Submitted At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($reviews as $review)
                        <tr>
                            <td class="px-4 py-4 font-semibold text-slate-950">{{ $review->event->name ?? '—' }}</td>
                            <td class="px-4 py-4">
                                @if($review->contact)
                                    <a href="{{ route('admin.contacts.show', $review->contact) }}" class="text-[#4E2E36] hover:underline font-semibold">
                                        {{ $review->contact->name ?: $review->contact->phone }}
                                    </a>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-slate-600 whitespace-pre-wrap max-w-md">{{ $review->experience }}</td>
                            <td class="px-4 py-4 text-slate-500">{{ $review->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-slate-500">
                                No reviews have been submitted yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $reviews->links() }}
        </div>
    </section>
</x-admin.layout>