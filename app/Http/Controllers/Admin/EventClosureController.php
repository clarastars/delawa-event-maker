<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CloseEventRequest;
use App\Models\Event;
use App\Services\EventClosureMetrics;
use App\Services\EventClosureReportGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventClosureController extends Controller
{
    public function __construct(
        private EventClosureMetrics $metrics,
        private EventClosureReportGenerator $reportGenerator,
    ) {}

    public function create(Event $event): View|RedirectResponse
    {
        if ($event->isClosed()) {
            return redirect()->route('admin.events.closure.show', $event);
        }

        $metrics = $this->metrics->forEvent($event);

        return view('admin.events.close', [
            'event' => $event,
            'metrics' => $metrics,
        ]);
    }

    public function store(CloseEventRequest $request, Event $event): RedirectResponse
    {
        if ($event->isClosed()) {
            return redirect()->route('admin.events.closure.show', $event);
        }

        $metrics = $this->metrics->forEvent($event);

        $closureNotes = [
            'observations' => $request->validated('closure_observations'),
            'lessons_learned' => $request->validated('closure_lessons_learned'),
            'recommendations' => $request->validated('closure_recommendations'),
        ];

        $paths = $this->reportGenerator->generate($event, $request->user(), $metrics, $closureNotes);

        $event->update([
            'closed_at' => now(),
            'closed_by_user_id' => $request->user()->id,
            'closure_observations' => $closureNotes['observations'],
            'closure_lessons_learned' => $closureNotes['lessons_learned'],
            'closure_recommendations' => $closureNotes['recommendations'],
            'closure_pdf_path' => $paths['pdf_path'],
            'closure_register_path' => $paths['register_path'],
        ]);

        return redirect()
            ->route('admin.events.closure.show', $event)
            ->with('status', 'Event closed. Executive summary and voucher register are ready to download.');
    }

    public function show(Event $event): View|RedirectResponse
    {
        if (! $event->isClosed()) {
            return redirect()->route('admin.events.close.create', $event);
        }

        $event->load('closedBy');

        return view('admin.events.closure', [
            'event' => $event,
            'metrics' => $this->metrics->forEvent($event),
        ]);
    }

    public function regeneratePdf(Event $event): RedirectResponse
    {
        if (! $event->isClosed()) {
            return redirect()->route('admin.events.show', $event);
        }

        $event->load('closedBy');
        $metrics = $this->metrics->forEvent($event);

        $closureNotes = [
            'observations' => $event->closure_observations,
            'lessons_learned' => $event->closure_lessons_learned,
            'recommendations' => $event->closure_recommendations,
        ];

        if ($event->closure_pdf_path !== null) {
            Storage::disk('local')->delete($event->closure_pdf_path);
        }

        $paths = $this->reportGenerator->generate(
            $event,
            $event->closedBy ?? auth()->user(),
            $metrics,
            $closureNotes,
        );

        $event->update(['closure_pdf_path' => $paths['pdf_path']]);

        return redirect()
            ->route('admin.events.closure.show', $event)
            ->with('status', 'Executive summary PDF regenerated with your saved notes.');
    }

    public function downloadPdf(Event $event): StreamedResponse|RedirectResponse
    {
        if (! $event->isClosed() || $event->closure_pdf_path === null) {
            return redirect()->route('admin.events.show', $event);
        }

        if (! Storage::disk('local')->exists($event->closure_pdf_path)) {
            return redirect()
                ->route('admin.events.closure.show', $event)
                ->withErrors(['event' => 'The closure PDF could not be found.']);
        }

        return Storage::disk('local')->download(
            $event->closure_pdf_path,
            basename($event->closure_pdf_path),
            ['Content-Type' => 'application/pdf']
        );
    }

    public function downloadRegister(Event $event): StreamedResponse|RedirectResponse
    {
        if (! $event->isClosed() || $event->closure_register_path === null) {
            return redirect()->route('admin.events.show', $event);
        }

        if (! Storage::disk('local')->exists($event->closure_register_path)) {
            return redirect()
                ->route('admin.events.closure.show', $event)
                ->withErrors(['event' => 'The voucher register could not be found.']);
        }

        return Storage::disk('local')->download(
            $event->closure_register_path,
            basename($event->closure_register_path),
            ['Content-Type' => 'text/csv']
        );
    }
}
