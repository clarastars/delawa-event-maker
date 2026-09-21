@props([
    'locale',
    'dir',
    'nextLocale',
    'title',
    'description',
])

@php
    $ogUrl = url()->current();
    $ogImage = asset('images/logo.png');
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#006C35">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="format-detection" content="telephone=no">
        <title>{{ $title }}</title>
        <meta name="description" content="{{ $description }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $ogUrl }}">
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:locale" content="{{ $locale === 'ar' ? 'ar_SA' : 'en_US' }}">
        <meta property="og:site_name" content="Delawa - ديلاوة">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon_32x32.png') }}">
        <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('favicon_128x128.png') }}">
        @fonts
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/accept-phone.js', 'resources/js/accept-otp.js'])
        @endif
    </head>
    <body class="min-h-dvh bg-[#006C35] text-slate-950 antialiased touch-manipulation" style="-webkit-tap-highlight-color: transparent;">
        <div class="mx-auto flex min-h-dvh w-full max-w-md flex-col px-4 pb-[max(1.5rem,env(safe-area-inset-bottom))] pt-[max(1rem,env(safe-area-inset-top))]">
            <header class="mb-4 flex items-center justify-between gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Delawa" class="h-12 w-12 rounded-full border-2 border-white bg-white shadow-md">
                <a
                    href="{{ request()->fullUrlWithQuery(['lang' => $nextLocale]) }}"
                    class="inline-flex min-h-11 items-center rounded-full bg-white/95 px-4 text-sm font-semibold text-slate-800 shadow-sm"
                >
                    {{ $locale === 'ar' ? 'English' : 'العربية' }}
                </a>
            </header>

            {{ $slot }}
        </div>
    </body>
</html>
