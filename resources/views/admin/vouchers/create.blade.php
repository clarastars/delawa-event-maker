<x-admin.layout title="Create Voucher">
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black">Create Voucher</h1>
                <p class="mt-2 text-sm text-slate-500">Add a voucher that can later be assigned to an uploaded contact.</p>
            </div>
            <a href="{{ route('admin.vouchers.index') }}" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Back</a>
        </div>

        <form method="POST" action="{{ route('admin.vouchers.store') }}" class="space-y-8">
            @csrf
            @include('admin.vouchers._form')

            <button class="rounded-2xl bg-[#7D4651] px-6 py-3 font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                Save Voucher
            </button>
        </form>
    </section>
</x-admin.layout>
