@php
    $copy = [
        'en' => [
            'title' => 'Your voucher',
            'voucher_alt' => 'Delawa purchase voucher',
            'download' => 'Download voucher',
            'one_time' => 'One-time use',
            'remaining_balance' => 'Remaining balance',
            'currency' => 'SAR',
            'branch_heading' => 'Our branch:',
            'branch_en' => 'Al Narjis',
            'branch_ar' => 'فرع النرجس',
        ],
        'ar' => [
            'title' => 'قسيمتك',
            'voucher_alt' => 'قسيمة شراء تسيپاس',
            'download' => 'تحميل القسيمة',
            'one_time' => 'استخدام لمرة واحدة',
            'remaining_balance' => 'الرصيد المتبقي',
            'currency' => 'ريال',
            'branch_heading' => 'فرعنا:',
            'branch_en' => 'Al Narjis',
            'branch_ar' => 'فرع النرجس',
        ],
    ][$locale];

    $branchUrl = 'https://maps.app.goo.gl/MS1gm2sfdZwW8xBK7';

    $dir = $locale === 'ar' ? 'rtl' : 'ltr';
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $copy['title'] }} - Delawa</title>
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon_16x16.png') }}">
        @fonts
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/voucher.js'])
        @endif
    </head>
    <body class="min-h-screen bg-[#7D4651] text-slate-950 antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-6xl flex-col items-center justify-center px-6 py-10">
            <div class="w-full max-w-lg">
            <div
                id="voucher-card"
                data-voucher-id="{{ $voucher->voucher_id }}"
                class="w-full overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-slate-900/25 ring-1 ring-white/60"
            >
                <img
                    src="{{ asset('voucher.webp') }}"
                    alt="{{ $copy['voucher_alt'] }}"
                    width="1200"
                    height="630"
                    class="block h-auto w-full"
                >

                <div class="border-t border-slate-100 px-6 py-6 text-center">
                    <svg id="voucher-barcode" class="mx-auto h-12 w-full max-w-[14rem]" aria-hidden="true"></svg>
                    <p data-voucher-id-label class="mt-2 font-mono text-sm font-semibold tracking-wide text-slate-950" dir="ltr">{{ $voucher->voucher_id }}</p>

                    @if ($remainingBalance !== null)
                        <p class="mt-4 text-base font-bold text-[#4E2E36]">
                            {{ $copy['remaining_balance'] }}:
                            <span dir="ltr">{{ number_format($remainingBalance, 2) }}</span>
                            {{ $copy['currency'] }}
                        </p>
                    @endif

                    @if ($voucher->one_time_redemption)
                        <p data-one-time-label class="mt-3 text-xs font-semibold text-[#4E2E36]">{{ $copy['one_time'] }}</p>
                    @endif
                </div>
            </div>

            <button
                id="download-voucher"
                type="button"
                class="mt-8 w-full rounded-2xl bg-white px-6 py-4 text-base font-bold text-[#4E2E36] shadow-lg shadow-slate-900/15 transition hover:bg-slate-50 disabled:opacity-60"
            >
                {{ $copy['download'] }}
            </button>
            </div>

            <section class="mt-10 w-full text-center text-white">
                <h2 class="text-sm font-bold uppercase tracking-widest text-white/90">
                    {{ $copy['branch_heading'] }}
                </h2>
                <a
                    href="{{ $branchUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group mx-auto mt-6 inline-flex flex-col items-center gap-2 rounded-2xl px-4 py-3 transition hover:bg-white/10 sm:gap-3"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6 shrink-0 text-white sm:size-8" aria-hidden="true">
                        <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-xs leading-snug text-white group-hover:text-white/90 sm:text-base">
                        <span class="font-semibold">{{ $copy['branch_ar'] }}</span><br>
                        <span class="font-medium text-white/90">{{ $copy['branch_en'] }}</span>
                    </span>
                </a>
            </section>
        </main>
    </body>
</html>
