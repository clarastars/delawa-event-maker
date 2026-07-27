<x-admin.layout title="Event Closure Report">
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('admin.events.index') }}" class="text-sm font-semibold text-[#4E2E36] hover:underline">&larr; Back to events</a>
                <p class="mt-4 text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Closure package</p>
                <h1 class="mt-2 text-3xl font-black">{{ $event->name }}</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Closed {{ $event->closed_at?->format('Y-m-d H:i') }}
                    @if ($event->closedBy)
                        by {{ $event->closedBy->name ?: $event->closedBy->email }}
                    @endif
                </p>
            </div>
            <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-bold uppercase tracking-wide text-slate-600 ring-1 ring-slate-200">
                Closed
            </span>
        </div>

        @if ($errors->has('event'))
            <div class="mb-6 rounded-2xl bg-red-50 p-4 text-sm font-medium text-red-800 ring-1 ring-red-200">
                {{ $errors->first('event') }}
            </div>
        @endif

        <div class="mb-8 flex flex-wrap gap-3">
            <a href="{{ route('admin.events.closure.pdf', $event) }}" class="rounded-2xl bg-[#7D4651] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                Download executive summary (PDF)
            </a>
            <form method="POST" action="{{ route('admin.events.closure.pdf.regenerate', $event) }}">
                @csrf
                <button class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:border-[#7D4651] hover:text-[#4E2E36]">
                    Regenerate PDF from saved notes
                </button>
            </form>
            <a href="{{ route('admin.events.closure.register', $event) }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:border-[#7D4651] hover:text-[#4E2E36]">
                Download voucher register (CSV)
            </a>
            <a href="{{ route('admin.events.show', $event) }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                View event details
            </a>
        </div>

        <div class="mb-8">
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Final performance snapshot</h2>
            <div class="mt-4">
                @include('admin.events.partials.closure-metrics', ['metrics' => $metrics])
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 lg:col-span-1">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Observations</h2>
                <p class="mt-4 whitespace-pre-wrap text-sm leading-relaxed text-slate-700">{{ $event->closure_observations ?: '—' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 lg:col-span-1">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Lessons learned</h2>
                <p class="mt-4 whitespace-pre-wrap text-sm leading-relaxed text-slate-700">{{ $event->closure_lessons_learned ?: '—' }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-6 lg:col-span-1">
                <h2 class="text-sm font-bold uppercase tracking-wide text-emerald-800">Recommendations</h2>
                <p class="mt-4 whitespace-pre-wrap text-sm leading-relaxed text-emerald-950">{{ $event->closure_recommendations ?: '—' }}</p>
            </div>
        </div>
    </section>
</x-admin.layout>
