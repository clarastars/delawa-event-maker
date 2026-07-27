<x-admin.layout title="Add Event">
    <section class="mx-auto max-w-xl rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8">
            <a href="{{ route('admin.events.index') }}" class="text-sm font-semibold text-[#4E2E36] hover:underline">&larr; Back to events</a>
            <h1 class="mt-2 text-3xl font-black">Add Event</h1>
            <p class="mt-2 text-sm text-slate-500">Give the event a name. You can upload coupons and a banner right after.</p>
        </div>

        <form method="POST" action="{{ route('admin.events.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700">Event name</label>
                <input id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Ramadan Campaign 2027" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required autofocus>
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button class="w-full rounded-2xl bg-[#7D4651] px-6 py-3 font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                Create event
            </button>
        </form>
    </section>
</x-admin.layout>
