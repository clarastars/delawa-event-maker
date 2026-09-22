@php
    $copy = [
        'en' => [
            'title' => "You're with us!",
            'body' => 'Thanks for joining National Day 96. You can now show this message to the cashier and enjoy our offers!',
            'privacy' => 'Read the Privacy Notice',
            'back' => 'Back to form',
        ],
        'ar' => [
            'title' => 'أنت معنا!',
            'body' => 'شكرًا لانضمامك لليوم الوطني 96. تقدر الان تظهر هذه الرسالة الى الكاشير و الاستفادة من عروضنا!',
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
            <p class="mt-6 flex flex-col gap-2 text-sm text-slate-500">
                <a href="{{ route('sa96.privacy', ['lang' => $locale]) }}" class="underline underline-offset-2">{{ $copy['privacy'] }}</a>
                <a href="{{ route('sa96.create', ['lang' => $locale]) }}" class="underline underline-offset-2">{{ $copy['back'] }}</a>
            </p>
        </section>
    </main>
</x-sa96-layout>
