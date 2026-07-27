<x-admin.layout title="Close Event">
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8">
            <a href="{{ route('admin.events.index') }}" class="text-sm font-semibold text-[#4E2E36] hover:underline">&larr; Back to events</a>
            <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Project closeout</p>
                    <h1 class="mt-2 text-3xl font-black">{{ $event->name }}</h1>
                    <p class="mt-2 max-w-3xl text-sm text-slate-600">
                        Complete formal event closure using PMP-style closeout: validate outcomes, document observations,
                        capture lessons learned, and prepare an executive package for leadership review.
                    </p>
                </div>
                <span class="rounded-full bg-amber-50 px-4 py-2 text-xs font-bold uppercase tracking-wide text-amber-800 ring-1 ring-amber-200">
                    Open event
                </span>
            </div>
        </div>

        <div class="mb-8 grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 lg:col-span-1">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Closeout checklist</h2>
                <ol class="mt-4 space-y-3 text-sm text-slate-700">
                    <li class="flex gap-3"><span class="font-black text-[#4E2E36]">1.</span> Review performance metrics and voucher outcomes.</li>
                    <li class="flex gap-3"><span class="font-black text-[#4E2E36]">2.</span> Record observations from delivery and guest experience.</li>
                    <li class="flex gap-3"><span class="font-black text-[#4E2E36]">3.</span> Capture lessons learned for the next event cycle.</li>
                    <li class="flex gap-3"><span class="font-black text-[#4E2E36]">4.</span> Draft recommendations to justify future budget and events.</li>
                    <li class="flex gap-3"><span class="font-black text-[#4E2E36]">5.</span> Confirm closure to disable the public invite link.</li>
                </ol>
            </div>

            <div class="rounded-2xl border border-[#7D4651]/20 bg-[#7D4651]/5 p-5 lg:col-span-2">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Deliverables on closure</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200">
                        <p class="font-bold text-slate-950">Executive summary (PDF)</p>
                        <p class="mt-1 text-sm text-slate-600">One-page leadership report with budget, utilization, and your closeout notes.</p>
                    </div>
                    <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200">
                        <p class="font-bold text-slate-950">Voucher register (Excel-ready CSV)</p>
                        <p class="mt-1 text-sm text-slate-600">Full card-level detail: assignment, activation, balances, and utilization status.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Performance snapshot</h2>
            <div class="mt-4">
                @include('admin.events.partials.closure-metrics', ['metrics' => $metrics])
            </div>
            @if ($metrics['values']['untracked_value'] > 0)
                <p class="mt-3 text-sm text-amber-800">
                    {{ number_format($metrics['values']['untracked_value'], 2) }} SAR in activated cards still need a balance sync.
                    Run <code class="rounded bg-amber-100 px-1.5 py-0.5 text-xs">php artisan tsepass:sync-activated-balances</code> before closing if you want the most accurate utilization figures.
                </p>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.events.close.store', $event) }}" class="space-y-6">
            @csrf

            <div class="rounded-2xl border border-slate-200 p-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Observations & comments</h2>
                <p class="mt-1 text-sm text-slate-600">What happened during the event? Note guest flow, operational issues, or stakeholder feedback.</p>
                <textarea
                    name="closure_observations"
                    rows="4"
                    class="mt-4 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                    placeholder="Example: OTP verification worked smoothly. 12% of guests activated on the same day as distribution."
                >{{ old('closure_observations') }}</textarea>
                @error('closure_observations')
                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-slate-200 p-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Lessons learned</h2>
                <p class="mt-1 text-sm text-slate-600">What should we repeat or improve in the next event cycle?</p>
                <textarea
                    name="closure_lessons_learned"
                    rows="4"
                    class="mt-4 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                    placeholder="Example: Pre-assign vouchers 48 hours earlier to reduce same-day support load."
                >{{ old('closure_lessons_learned') }}</textarea>
                @error('closure_lessons_learned')
                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-emerald-800">Recommendations for future events</h2>
                <p class="mt-1 text-sm text-emerald-900/80">
                    Use this section to support your case for additional events, expanded guest lists, or increased gift-card budget.
                </p>
                <textarea
                    name="closure_recommendations"
                    rows="4"
                    class="mt-4 w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20"
                    placeholder="Example: Based on 87% activation and strong guest feedback, recommend a Q4 event with 20% higher voucher budget."
                >{{ old('closure_recommendations') }}</textarea>
                @error('closure_recommendations')
                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                <label class="flex items-start gap-3 text-sm text-slate-700">
                    <input
                        type="checkbox"
                        name="confirmed"
                        value="1"
                        @checked(old('confirmed'))
                        class="mt-1 rounded border-slate-300 text-[#7D4651] focus:ring-[#7D4651]"
                    >
                    <span>
                        <span class="font-bold text-slate-950">I confirm this event is complete.</span>
                        The public invite link will be disabled, and the executive PDF plus voucher register will be generated for administrator reporting.
                    </span>
                </label>
                @error('confirmed')
                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <button class="rounded-2xl bg-slate-950 px-6 py-3 text-sm font-bold text-white hover:bg-slate-800">
                    Close event &amp; generate reports
                </button>
                <a href="{{ route('admin.events.index') }}" class="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </section>
</x-admin.layout>
