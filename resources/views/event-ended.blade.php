@php
    $copy = [
        'en' => [
            'switch' => 'العربية',
            'title' => 'This event has ended',
            'message' => 'Thank you for joining us at this Delawa event. Voucher registration is now closed.',
            'branch' => 'Visit us at Al Narjis branch',
            'logout' => 'Log out',
        ],
        'ar' => [
            'switch' => 'English',
            'title' => 'انتهى الحدث',
            'message' => 'شكراً لحضوركم في فعالية ديلاوة. التسجيل للحصول على القسيمة مغلق الآن.',
            'branch' => 'زورونا في فرع النرجس',
            'logout' => 'تسجيل الخروج',
        ],
    ][$locale];

    $dir = $locale === 'ar' ? 'rtl' : 'ltr';
    $nextLocale = $locale === 'ar' ? 'en' : 'ar';
    $branchUrl = 'https://maps.app.goo.gl/MS1gm2sfdZwW8xBK7';
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Delawa - ديلاوة</title>
        <meta name="robots" content="noindex, nofollow">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon_32x32.png') }}">
        @fonts
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css'])
        @endif
    </head>
    <body class="flex min-h-screen flex-col bg-[#7D4651] text-slate-950 antialiased">
        <main class="mx-auto flex w-full max-w-2xl flex-1 flex-col items-center justify-center px-6 py-10">
            <section class="w-full rounded-[2rem] bg-white/95 p-8 shadow-2xl shadow-slate-900/20 md:p-10">
                <div class="flex justify-end">
                    <a href="{{ route('home', ['lang' => $nextLocale]) }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-[#7D4651] hover:text-[#4E2E36]">
                        {{ $copy['switch'] }}
                    </a>
                </div>

                <div class="mt-6 text-center">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="Delawa"
                        width="180"
                        height="180"
                        class="mx-auto mb-8 h-auto w-full max-w-[180px] rounded-full border-4 border-white shadow-lg"
                    >

                    <h1 class="text-3xl font-black text-slate-900 md:text-4xl">{{ $copy['title'] }}</h1>
                    <p class="mt-4 text-base leading-7 text-slate-600 md:text-lg">{{ $copy['message'] }}</p>

                    <a
                        href="{{ $branchUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-8 inline-flex items-center gap-2 rounded-2xl bg-[#7D4651] px-6 py-3 text-base font-bold text-white shadow-lg shadow-[#7D4651]/25 transition hover:bg-[#6A3A44]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 shrink-0" aria-hidden="true">
                            <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                        </svg>
                        {{ $copy['branch'] }}
                    </a>
                </div>
            </section>
        </main>

        @auth
            <footer class="pb-4 text-center">
                <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-[10px] text-white/50 transition hover:text-white/80">
                        {{ $copy['logout'] }}
                    </button>
                </form>
            </footer>
        @endauth
    </body>
</html>
