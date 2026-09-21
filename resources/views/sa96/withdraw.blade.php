@php
    $copy = [
        'en' => [
            'title' => 'Withdraw consent',
            'lead' => 'This is as easy as registering. Enter the same mobile number. We will delete your name, number, and year of birth if they are on file. We will not tell you whether the number was registered.',
            'phone' => 'Mobile number',
            'phone_placeholder' => '5XXXXXXXX',
            'confirm' => 'I want to withdraw my consent and delete my personal data for the Saudi National Day 96 campaign, including optional marketing if I had agreed to it.',
            'submit' => 'Withdraw and delete',
            'back' => 'Back to registration',
        ],
        'ar' => [
            'title' => 'سحب الموافقة',
            'lead' => 'هذه الخطوة بنفس سهولة التسجيل. أدخل رقم الجوال نفسه. سنحذف اسمك ورقمك وسنة ميلادك إن وُجدت. لن نخبرك ما إذا كان الرقم مسجّلاً أم لا.',
            'phone' => 'رقم الجوال',
            'phone_placeholder' => '5XXXXXXXX',
            'confirm' => 'أريد سحب موافقتي وحذف بياناتي الشخصية لحملة اليوم الوطني السعودي 96، بما في ذلك التسويق الاختياري إن كنت قد وافقت عليه.',
            'submit' => 'سحب وحذف',
            'back' => 'العودة للتسجيل',
        ],
    ][$locale];

    $inputClass = 'min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-base text-slate-950 shadow-sm outline-none focus:border-[#006C35] focus:ring-4 focus:ring-[#006C35]/20';
@endphp

<x-sa96-layout
    :locale="$locale"
    :dir="$dir"
    :next-locale="$nextLocale"
    :title="$copy['title'].' — Delawa'"
    :description="$copy['lead']"
>
    <main class="flex flex-1 flex-col">
        <section class="rounded-[1.75rem] bg-white/95 p-5 shadow-2xl shadow-black/20">
            <h1 class="text-2xl text-slate-950">{{ $copy['title'] }}</h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $copy['lead'] }}</p>

            @if (session('status'))
                <p class="mt-4 rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-900 ring-1 ring-emerald-200" role="status">
                    {{ session('status') }}
                </p>
            @endif

            <form
                id="sa96-form"
                method="POST"
                action="{{ route('sa96.withdraw.store') }}"
                class="mt-6 flex flex-col gap-5"
                data-locale="{{ $locale }}"
                novalidate
            >
                @csrf
                <input type="hidden" name="lang" value="{{ $locale }}">

                <div id="phone-field">
                    <label for="phone" class="mb-2 block text-sm font-semibold text-slate-800">{{ $copy['phone'] }}</label>
                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        inputmode="tel"
                        autocomplete="tel"
                        value="{{ old('phone') }}"
                        placeholder="{{ $copy['phone_placeholder'] }}"
                        class="{{ $inputClass }}"
                    >
                    <p id="phone-client-error" class="mt-2 hidden text-sm font-medium text-red-700" role="alert"></p>
                    @error('phone')
                        <p class="mt-2 text-sm font-medium text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex min-h-12 cursor-pointer items-start gap-3 rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200">
                    <input
                        type="checkbox"
                        name="confirm_withdraw"
                        value="1"
                        class="mt-1 size-6 shrink-0 rounded border-slate-300 text-[#006C35] accent-[#006C35]"
                    >
                    <span class="text-sm leading-6 text-slate-700">{{ $copy['confirm'] }}</span>
                </label>
                @error('confirm_withdraw')
                    <p class="text-sm font-medium text-red-700" role="alert">{{ $message }}</p>
                @enderror

                <button type="submit" class="min-h-12 w-full rounded-2xl bg-slate-950 px-4 text-base font-semibold text-white">
                    {{ $copy['submit'] }}
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-500">
                <a href="{{ route('sa96.create', ['lang' => $locale]) }}" class="underline underline-offset-2">{{ $copy['back'] }}</a>
            </p>
        </section>
    </main>
</x-sa96-layout>
