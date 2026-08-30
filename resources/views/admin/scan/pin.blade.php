<x-admin.layout title="Scanner PIN">
    <section class="mx-auto max-w-md rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-6 flex flex-col items-center text-center">
            <img src="{{ asset('images/logo.png') }}" alt="Delawa" class="mb-4 h-20 w-20 rounded-full border-4 border-[#7D4651]/20 shadow-sm">
            <h1 class="text-3xl font-black text-[#4E2E36]">Scanner Access</h1>
            <p class="mt-1 text-sm text-slate-500">ديلاوة</p>
        </div>
        <p class="text-center text-sm text-slate-500">Enter the scanner PIN to unlock voucher scanning. No login required.</p>

        <form method="POST" action="{{ route('admin.scan.pin.store') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <label for="pin" class="block text-sm font-semibold text-slate-700">PIN</label>
                <input
                    id="pin"
                    name="pin"
                    type="password"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="8"
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-center text-2xl tracking-[0.4em] outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                    required
                    autofocus
                >
                @error('pin')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button class="w-full rounded-2xl bg-[#7D4651] px-5 py-3 font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                Unlock Scanner
            </button>
        </form>
    </section>
</x-admin.layout>
