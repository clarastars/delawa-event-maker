<x-admin.layout title="Contacts">
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-10">
            <h1 class="text-3xl font-black">Contacts</h1>
            <p class="mt-2 text-sm text-slate-500">Add invitees individually or import a CSV. Optionally pick an event to hand each contact one of its available coupons.</p>
        </div>

        <div class="grid gap-10 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                <h2 class="text-xl font-bold text-slate-950">Add contact</h2>
                <p class="mt-2 text-sm text-slate-500">Enter one invitee at a time.</p>

                <form method="POST" action="{{ route('admin.contacts.store') }}" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-700">Name</label>
                        <input id="name" name="name" value="{{ old('name') }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-semibold text-slate-700">Phone</label>
                        <input id="phone" name="phone" value="{{ old('phone') }}" placeholder="05xxxxxxxx or +9665xxxxxxxx" dir="ltr" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required>
                        @error('phone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="event_id" class="block text-sm font-semibold text-slate-700">Assign a coupon from event (optional)</label>
                        <select id="event_id" name="event_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20">
                            <option value="">No coupon assignment</option>
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}" @selected(old('event_id') == $event->id)>{{ $event->name }}</option>
                            @endforeach
                        </select>
                        @error('event_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button class="w-full rounded-2xl bg-[#7D4651] px-6 py-3 font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                        Save contact
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 p-6">
                <h2 class="text-xl font-bold text-slate-950">Upload CSV</h2>
                <p class="mt-2 text-sm text-slate-500">Bulk import from a spreadsheet export.</p>

                <form method="POST" action="{{ route('admin.contacts.upload.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                    @csrf

                    <div>
                        <label for="contacts" class="block text-sm font-semibold text-slate-700">Contacts CSV</label>
                        <input id="contacts" name="contacts" type="file" accept=".csv,text/csv,text/plain" class="mt-2 w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm" required>
                        @error('contacts')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="upload_event_id" class="block text-sm font-semibold text-slate-700">Assign coupons from event (optional)</label>
                        <select id="upload_event_id" name="event_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20">
                            <option value="">No coupon assignment</option>
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600 ring-1 ring-slate-200">
                        Header row: <strong>{{ implode(', ', $expectedHeaders) }}</strong>, or columns in that order.
                    </div>

                    <pre class="overflow-x-auto rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-emerald-300" dir="ltr">{{ $sampleCsv }}</pre>

                    <div class="flex flex-wrap gap-3">
                        <button class="flex-1 rounded-2xl border border-[#7D4651] bg-white px-6 py-3 font-bold text-[#4E2E36] transition hover:bg-[#7D4651]/5">
                            Upload contacts
                        </button>
                        <a href="{{ route('admin.contacts.upload.sample') }}" class="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 hover:border-[#7D4651] hover:text-[#4E2E36]">
                            Download sample CSV
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-admin.layout>
