<x-admin.layout title="Edit Team Member">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Edit Team Member: {{ $user->name }}</h1>
        <a href="{{ route('admin.team.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Back to Team
        </a>
    </div>

    <form action="{{ route('admin.team.update', $user) }}" method="POST" class="max-w-2xl rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required>
                @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required>
                @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                <input type="password" name="password" id="password" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" minlength="8">
                <p class="mt-2 text-sm text-slate-500">Leave blank to keep current password.</p>
                @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="role" class="block text-sm font-semibold text-slate-700">Role</label>
                <select name="role" id="role" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required>
                    <option value="admin" @selected(old('role', $user->role) == 'admin')>Admin</option>
                    <option value="scanner" @selected(old('role', $user->role) == 'scanner')>Scanner</option>
                </select>
                @error('role') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4">
                <button type="submit" class="rounded-full bg-[#7D4651] px-8 py-3 font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                    Update Member
                </button>
            </div>
        </div>
    </form>
</x-admin.layout>
