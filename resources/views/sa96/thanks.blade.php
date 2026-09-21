@php
    $copy = [
        'en' => [
            'title' => "You're in!",
            'body' => 'Thanks for joining National Day 96. You can opt out or delete your data anytime.',
            'withdraw' => 'Withdraw consent / delete my data',
            'privacy' => 'Read the Privacy Notice',
            'back' => 'Back to form',
        ],
        'ar' => [
            'title' => 'أنت معنا!',
            'body' => 'شكرًا لانضمامك لليوم الوطني 96. تقدر تلغي أو تحذف بياناتك في أي وقت.',
            'withdraw' => 'سحب الموافقة / حذف بياناتي',
            'privacy' => 'قراءة إشعار الخصوصية',
            'back' => 'العودة للنموذج',
        ],
    ][$locale];
@endphp

<x-sa96-layout
    :locale="$locale"
    :dir="$dir"
    :next-locale="$nextLocale"
    :title="$copy['title'].' — Delawa'"
    :description="$copy['body']"
>
    <main class="flex flex-1 flex-col justify-center">
        <section class="rounded-[1.75rem] bg-white/95 p-6 text-center shadow-2xl shadow-black/20">
            <h1 class="text-2xl text-slate-950">{{ $copy['title'] }}</h1>
            <p class="mt-3 text-base leading-relaxed text-slate-600">{{ $copy['body'] }}</p>
            <a
                href="{{ route('sa96.withdraw', ['lang' => $locale]) }}"
                class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-slate-950 px-4 text-base font-semibold text-white"
            >
                {{ $copy['withdraw'] }}
            </a>
            <p class="mt-4 flex flex-col gap-2 text-sm text-slate-500">
                <a href="{{ route('sa96.privacy', ['lang' => $locale]) }}" class="underline underline-offset-2">{{ $copy['privacy'] }}</a>
                <a href="{{ route('sa96.create', ['lang' => $locale]) }}" class="underline underline-offset-2">{{ $copy['back'] }}</a>
            </p>
        </section>
    </main>
</x-sa96-layout>
