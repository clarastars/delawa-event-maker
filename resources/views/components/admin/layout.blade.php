<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Admin' }} - Delawa - ديلاوة</title>
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon_16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon_32x32.png') }}">
        <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon_48x48.png') }}">
        <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon_64x64.png') }}">
        <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('favicon_128x128.png') }}">
        <link rel="icon" type="image/png" sizes="256x256" href="{{ asset('favicon_256x256.png') }}">
        @fonts
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
            @stack('vite')
        @endif
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-950 antialiased">
        <div class="mx-auto min-h-screen w-full max-w-6xl px-6 py-8">
            <header class="mb-8 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 text-2xl font-black text-[#4E2E36]">
                        <img src="{{ asset('images/logo.png') }}" alt="Delawa" class="h-10 w-10 rounded-full border-2 border-white shadow-sm">
                        <span>Delawa Admin <span class="font-normal text-base text-slate-500">ديلاوة</span></span>
                    </a>
                    <p class="text-sm text-slate-500">Manage Delawa events, vouchers, and invite contacts.</p>
                </div>

                @auth
                    <nav class="flex flex-wrap items-center gap-3 text-sm font-semibold">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.events.index') }}" class="rounded-full bg-white px-4 py-2 text-slate-700 shadow-sm ring-1 ring-slate-200 hover:text-[#4E2E36]">Events</a>
                            <a href="{{ route('admin.vouchers.index') }}" class="rounded-full bg-white px-4 py-2 text-slate-700 shadow-sm ring-1 ring-slate-200 hover:text-[#4E2E36]">Vouchers</a>
                            <a href="{{ route('admin.contacts.index') }}" class="rounded-full bg-white px-4 py-2 text-slate-700 shadow-sm ring-1 ring-slate-200 hover:text-[#4E2E36]">Contacts</a>
                            <a href="{{ route('admin.reviews.index') }}" class="rounded-full bg-white px-4 py-2 text-slate-700 shadow-sm ring-1 ring-slate-200 hover:text-[#4E2E36]">Reviews</a>
                            <a href="{{ route('admin.team.index') }}" class="rounded-full bg-white px-4 py-2 text-slate-700 shadow-sm ring-1 ring-slate-200 hover:text-[#4E2E36]">Team</a>
                        @endif
                        <a href="{{ route('admin.scan.index') }}" class="rounded-full bg-white px-4 py-2 text-slate-700 shadow-sm ring-1 ring-slate-200 hover:text-[#4E2E36] font-bold text-[#4E2E36]">Scanner</a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="rounded-full bg-slate-950 px-4 py-2 text-white">Log out</button>
                        </form>
                    </nav>
                @endauth
            </header>

            @if (session('status'))
                <div class="mb-6 rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-900 ring-1 ring-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </body>
</html>
