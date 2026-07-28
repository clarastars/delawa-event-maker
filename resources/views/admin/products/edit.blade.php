<x-admin.layout title="Edit Product">
    <section class="max-w-2xl mx-auto rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8">
            <a href="{{ route('admin.events.show', $event) }}" class="text-sm font-semibold text-[#4E2E36] hover:underline">&larr; Back to event</a>
            <h1 class="mt-2 text-3xl font-black">Edit Product</h1>
        </div>

        <form method="POST" action="{{ route('admin.events.products.update', [$event, $product]) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700">Product Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-950 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required autofocus>
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="image" class="block text-sm font-semibold text-slate-700">Image (Optional, max 4 MB)</label>
                @if($product->image_path)
                    <img src="{{ Storage::disk('public')->url($product->image_path) }}" class="mb-4 h-32 w-32 rounded bg-slate-100 object-cover">
                @endif
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm">
                @error('image')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4">
                <button class="rounded-2xl bg-[#7D4651] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                    Save Changes
                </button>
            </div>
        </form>
    </section>
</x-admin.layout>
