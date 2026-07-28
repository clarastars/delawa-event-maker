@php
    $copy = [
        'en' => [
            'title' => 'Your coupons',
            'greeting' => 'Welcome, :name.',
            'greeting_guest' => 'Welcome.',
            'intro_one' => 'Here is your coupon for :event.',
            'intro_many' => 'Here are your :count coupons for :event.',
            'remaining_balance' => 'Remaining balance',
            'balance' => 'Balance',
            'currency' => 'SAR',
            'expires' => 'Expires',
            'one_time' => 'One-time use',
            'review_title' => 'Write your experience',
            'review_prompt' => 'Tell us about your experience with this event...',
            'submit_review' => 'Submit',
        ],
        'ar' => [
            'title' => 'قسائمك',
            'greeting' => 'أهلاً :name.',
            'greeting_guest' => 'أهلاً بك.',
            'intro_one' => 'هذه قسيمتك لـ :event.',
            'intro_many' => 'هذه قسائمك (:count) لـ :event.',
            'remaining_balance' => 'الرصيد المتبقي',
            'balance' => 'الرصيد',
            'currency' => 'ريال',
            'expires' => 'تنتهي في',
            'one_time' => 'استخدام لمرة واحدة',
            'review_title' => 'اكتب تجربتك',
            'review_prompt' => 'أخبرنا عن تجربتك في هذه الفعالية...',
            'submit_review' => 'إرسال',
        ],
    ][$locale];

    $dir = $locale === 'ar' ? 'rtl' : 'ltr';

    $greeting = filled($contact->name)
        ? str_replace(':name', $contact->name, $copy['greeting'])
        : $copy['greeting_guest'];

    $intro = $vouchers->count() > 0 ? ($vouchers->count() === 1
        ? str_replace(':event', $event->name, $copy['intro_one'])
        : str_replace([':count', ':event'], [(string) $vouchers->count(), $event->name], $copy['intro_many'])) : str_replace(':event', $event->name, $copy['intro_one']);
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $copy['title'] }} - {{ $event->name }} - Delawa</title>
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon_16x16.png') }}">
        @fonts
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/event-vouchers.js'])
        @endif
        <script>
            document.addEventListener('submit', function (e) {
                setTimeout(function () {
                    if (!e.defaultPrevented && e.target.closest('form[data-loading-overlay]')) {
                        const overlay = document.getElementById('loading-overlay');
                        if (overlay) {
                            overlay.classList.remove('hidden');
                            overlay.classList.add('flex');
                        }
                    }
                }, 0);
            });
        </script>
    </head>
    <body class="min-h-screen bg-[#7D4651] text-slate-950 antialiased">
        <div id="loading-overlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-white/70 backdrop-blur-sm transition-opacity duration-300">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-[#7D4651] border-t-transparent"></div>
        </div>
        
        <main class="mx-auto flex min-h-screen w-full max-w-6xl flex-col items-center justify-center px-6 py-10">
            <div class="w-full max-w-lg">
                <header class="mb-8 text-center text-white">
                    <h1 class="text-2xl font-black">{{ $greeting }}</h1>
                    <p class="mt-2 text-sm font-medium text-white/90">{{ $intro }}</p>
                </header>

                @if ($event->bannerUrl())
                    <div class="mb-8 overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-slate-900/25 ring-1 ring-white/60">
                        <img
                            src="{{ $event->bannerUrl() }}"
                            alt="{{ $event->name }}"
                            class="block h-auto w-full"
                        >
                    </div>
                @endif
                
                @error('product')
                    <div class="mb-6 rounded-2xl bg-red-50 p-4 text-sm font-medium text-red-800 ring-1 ring-red-200">
                        {{ $message }}
                    </div>
                @enderror
                @if (session('status'))
                    <div class="mb-6 rounded-2xl bg-green-50 p-4 text-sm font-medium text-green-800 ring-1 ring-green-200">
                        {{ session('status') }}
                    </div>
                @enderror

                @if ($vouchers->isNotEmpty())
                    <div class="space-y-6 mb-8">
                        @foreach ($vouchers as $voucher)
                            <div class="w-full overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-slate-900/25 ring-1 ring-white/60">
                                <div class="px-6 py-6 text-center">
                                    <svg data-voucher-barcode="{{ $voucher->voucher_id }}" class="mx-auto h-12 w-full max-w-[14rem]" aria-hidden="true"></svg>
                                    <p class="mt-2 font-mono text-sm font-semibold tracking-wide text-slate-950" dir="ltr">{{ $voucher->voucher_id }}</p>

                                    @if ($remainingBalances[$voucher->id] !== null)
                                        <p class="mt-4 text-base font-bold text-[#4E2E36]">
                                            {{ $copy['remaining_balance'] }}:
                                            <span dir="ltr">{{ number_format($remainingBalances[$voucher->id], 2) }}</span>
                                            {{ $copy['currency'] }}
                                        </p>
                                    @else
                                        <p class="mt-4 text-base font-bold text-[#4E2E36]">
                                            {{ $copy['balance'] }}:
                                            <span dir="ltr">{{ number_format((float) $voucher->balance, 2) }}</span>
                                            {{ $copy['currency'] }}
                                        </p>
                                    @endif

                                    @if ($voucher->expiry_date)
                                        <p class="mt-2 text-xs font-semibold text-slate-500">
                                            {{ $copy['expires'] }}: <span dir="ltr">{{ $voucher->expiry_date->format('Y-m-d') }}</span>
                                        </p>
                                    @endif

                                    @if ($voucher->one_time_redemption)
                                        <p class="mt-3 text-xs font-semibold text-[#4E2E36]">{{ $copy['one_time'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($remainingEntries > 0 && $products->isNotEmpty())
                    <div class="mb-8">
                        <h2 class="mb-4 text-xl font-bold text-white text-center">
                            {{ $locale === 'ar' ? 'اختر منتجك' : 'Choose your product' }} 
                            ({{ $remainingEntries }} {{ $locale === 'ar' ? 'متبقي' : 'remaining' }})
                        </h2>
                        
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach($products as $product)
                                <div class="overflow-hidden rounded-2xl bg-white shadow-xl">
                                    @if ($product->image_path)
                                        <img src="{{ Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}" class="w-full object-cover">
                                    @else
                                        <div class="h-64 w-full bg-slate-100 flex items-center justify-center">
                                            <span class="text-slate-400">No Image</span>
                                        </div>
                                    @endif
                                    <div class="p-4 text-center">
                                        <h3 class="text-lg font-bold text-slate-900 mb-4">{{ $product->name }}</h3>
                                        <form data-loading-overlay method="POST" action="{{ route('event.products.claim', ['event' => $event, 'product' => $product, 'lang' => $locale]) }}">
                                            @csrf
                                            <button class="w-full rounded-xl bg-[#7D4651] px-4 py-2 font-bold text-white hover:bg-[#6A3A44]">
                                                {{ $locale === 'ar' ? 'اختيار' : 'Choose' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                @if ($remainingEntries === 0 && $vouchers->isNotEmpty() && !$hasReview)
                    <div class="mb-8 w-full overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-slate-900/25 ring-1 ring-white/60 p-6">
                        <h2 class="text-lg font-bold text-[#7D4651] mb-4 text-center">{{ $copy['review_title'] }}</h2>
                        <form data-loading-overlay method="POST" action="{{ route('event.vouchers.review', ['event' => $event, 'lang' => $locale]) }}">
                            @csrf
                            <textarea name="experience" rows="3" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#7D4651] focus:ring-[#7D4651] mb-4 text-sm" placeholder="{{ $copy['review_prompt'] }}" required></textarea>
                            @error('experience')
                                <p class="text-red-500 text-xs mb-4">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="w-full rounded-xl bg-[#7D4651] px-4 py-3 font-bold text-white hover:bg-[#6A3A44] transition-colors">
                                {{ $copy['submit_review'] }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </main>
    </body>
</html>
