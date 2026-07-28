<x-admin.layout title="Event">
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.events.index') }}" class="text-sm font-semibold text-[#4E2E36] hover:underline">&larr; Back to events</a>
                <h1 class="mt-2 text-3xl font-black">{{ $event->name }}</h1>
                <p class="mt-2 text-sm text-slate-500">Upload coupons and a banner, then share the invite link with your guests.</p>
            </div>
            <form
                method="POST"
                action="{{ route('admin.events.destroy', $event) }}"
                onsubmit="return confirm('Delete this event? This only works when it has no coupons.')"
            >
                @csrf
                @method('DELETE')
                <button class="rounded-2xl bg-red-50 px-5 py-3 text-sm font-bold text-red-700 ring-1 ring-red-200 hover:bg-red-100">
                    Delete event
                </button>
            </form>
        </div>

        @if ($errors->has('event'))
            <div class="mb-6 rounded-2xl bg-red-50 p-4 text-sm font-medium text-red-800 ring-1 ring-red-200">
                {{ $errors->first('event') }}
            </div>
        @endif

        @if ($event->isClosed())
            <div class="mb-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                This event is closed. The public invite link is disabled.
                <a href="{{ route('admin.events.closure.show', $event) }}" class="font-semibold text-[#4E2E36] underline">View closure report</a>
            </div>
        @endif

        <div class="mb-8 rounded-2xl border border-[#7D4651]/30 bg-[#7D4651]/5 p-6">
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Invite link</h2>
            <p class="mt-2 text-sm text-slate-600">Guests open this link, verify their phone with an OTP, and see their coupons.</p>
            <input
                type="text"
                value="{{ $inviteUrl }}"
                readonly
                onclick="this.select()"
                dir="ltr"
                class="mt-4 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 font-mono text-sm font-semibold text-[#4E2E36] outline-none"
            >
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Products</h2>
                    <p class="mt-2 text-sm text-slate-600">Add products to this event that attendees can pick from.</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('admin.events.products.create', $event) }}" class="rounded-2xl bg-[#7D4651] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                            Add product
                        </a>
                    </div>

                    @if ($event->products->isNotEmpty())
                        <div class="mt-6 flex flex-col gap-3">
                            @foreach ($event->products as $product)
                                <div class="flex items-center justify-between rounded-xl bg-white p-3 ring-1 ring-slate-200">
                                    <div class="flex items-center gap-3">
                                        @if ($product->image_path)
                                            <img src="{{ Storage::disk('public')->url($product->image_path) }}" class="h-10 w-10 rounded bg-slate-100 object-cover">
                                        @else
                                            <div class="h-10 w-10 rounded bg-slate-100"></div>
                                        @endif
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">{{ $product->name }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.events.products.edit', [$event, $product]) }}" class="text-sm font-medium text-[#7D4651] hover:underline">Edit</a>
                                        <form method="POST" action="{{ route('admin.events.products.destroy', [$event, $product]) }}" onsubmit="return confirm('Delete this product?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm font-medium text-red-600 hover:underline">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Coupons</h2>
                    <div class="mt-4 flex items-end gap-8">
                        <div>
                            <p class="text-3xl font-black text-slate-950">{{ $event->vouchers_count }}</p>
                            <p class="text-xs font-semibold text-slate-500">Total</p>
                        </div>
                        <div>
                            <p class="text-3xl font-black text-[#4E2E36]">{{ $event->assigned_vouchers_count }}</p>
                            <p class="text-xs font-semibold text-slate-500">Assigned</p>
                        </div>
                        <div>
                            <p class="text-3xl font-black text-slate-400">{{ $event->vouchers_count - $event->assigned_vouchers_count }}</p>
                            <p class="text-xs font-semibold text-slate-500">Available</p>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('admin.vouchers.upload.create', ['event' => $event->id]) }}" class="rounded-2xl bg-[#7D4651] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                            Upload coupons
                        </a>
                        <a href="{{ route('admin.vouchers.index', ['event' => $event->id]) }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:border-[#7D4651] hover:text-[#4E2E36]">
                            View coupons
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Event Details</h2>
                    <form method="POST" action="{{ route('admin.events.update', $event) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Name</label>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $event->name) }}"
                                required
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-950 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                            >
                            @error('name')
                                <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Google Maps Link</label>
                            <input
                                type="url"
                                name="maps_link"
                                value="{{ old('maps_link', $event->maps_link) }}"
                                placeholder="https://maps.google.com/..."
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-950 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                            >
                            @error('maps_link')
                                <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Maps Link Label</label>
                            <input
                                type="text"
                                name="maps_link_label"
                                value="{{ old('maps_link_label', $event->maps_link_label) }}"
                                placeholder="e.g. Go to Location"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-950 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                            >
                            @error('maps_link_label')
                                <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button class="w-full rounded-2xl bg-[#7D4651] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                            Save details
                        </button>
                    </form>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 p-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Banner</h2>

                @if ($event->bannerUrl())
                    <img
                        src="{{ $event->bannerUrl() }}"
                        alt="{{ $event->name }} banner"
                        class="mt-4 block h-auto w-full rounded-2xl ring-1 ring-slate-200"
                    >
                @else
                    <p class="mt-4 text-sm text-amber-800">No banner uploaded yet. The invite page will show the event name until you upload one.</p>
                @endif

                <form method="POST" action="{{ route('admin.events.banner.update', $event) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label for="banner" class="block text-sm font-semibold text-slate-700">Banner image (JPG, PNG, or WebP, max 4 MB)</label>
                        <input id="banner" name="banner" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm" required>
                        @error('banner')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button class="rounded-2xl bg-[#7D4651] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                        {{ $event->banner_path ? 'Replace banner' : 'Upload banner' }}
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-admin.layout>
