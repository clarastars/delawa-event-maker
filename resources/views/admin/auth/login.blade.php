<x-admin.layout title="Admin Login">
    <section class="mx-auto max-w-md rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-6 flex flex-col items-center text-center">
            <img src="{{ asset('images/logo.png') }}" alt="Delawa" class="mb-4 h-20 w-20 rounded-full border-4 border-[#7D4651]/20 shadow-sm">
            <h1 class="text-3xl font-black text-[#4E2E36]">Delawa Admin</h1>
            <p class="mt-1 text-sm text-slate-500">ديلاوة</p>
        </div>
        <p class="text-center text-sm text-slate-500">Use an existing admin account to manage Delawa events and vouchers.</p>

        <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required autofocus>
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                <input id="password" name="password" type="password" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required>
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-3 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-[#7D4651]">
                Remember me
            </label>

            <button class="w-full rounded-2xl bg-[#7D4651] px-5 py-3 font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                Log in
            </button>
        </form>
    </section>
</x-admin.layout>
