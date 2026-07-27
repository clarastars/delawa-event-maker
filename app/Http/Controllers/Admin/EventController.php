<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventBannerRequest;
use App\Http\Requests\EventRequest;
use App\Models\Event;
use App\Services\OngoingEventsReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    public function __construct(private OngoingEventsReport $ongoingEventsReport) {}

    public function index(): View
    {
        return view('admin.events.index', [
            'events' => Event::query()
                ->withCount([
                    'vouchers',
                    'vouchers as assigned_vouchers_count' => fn ($query) => $query->whereNotNull('contact_id'),
                ])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function currentReport(Event $event): View|RedirectResponse
    {
        if ($event->isClosed()) {
            return redirect()->route('admin.events.closure.show', $event);
        }

        return view('admin.events.current-report', [
            'event' => $event,
            'report' => $this->ongoingEventsReport->forEvent($event),
            'generatedAt' => now(),
        ]);
    }

    public function downloadStatement(Event $event): StreamedResponse|RedirectResponse
    {
        if ($event->isClosed()) {
            return redirect()->route('admin.events.closure.show', $event);
        }

        $csv = $this->ongoingEventsReport->statementCsv($event);

        return Response::streamDownload(
            function () use ($csv): void {
                echo $csv;
            },
            $this->ongoingEventsReport->statementFilename($event),
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function create(): View
    {
        return view('admin.events.create');
    }

    public function store(EventRequest $request): RedirectResponse
    {
        $event = Event::create([
            'name' => $request->validated('name'),
            'slug' => Event::generateUniqueSlug(),
        ]);

        return redirect()
            ->route('admin.events.show', $event)
            ->with('status', 'Event created. Upload coupons and a banner, then share the invite link.');
    }

    public function show(Event $event): View
    {
        $event->loadCount([
            'vouchers',
            'vouchers as assigned_vouchers_count' => fn ($query) => $query->whereNotNull('contact_id'),
        ]);

        return view('admin.events.show', [
            'event' => $event,
            'inviteUrl' => route('event.invite', $event),
        ]);
    }

    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        $event->update($request->validated());

        return redirect()
            ->route('admin.events.show', $event)
            ->with('status', 'Event updated.');
    }

    public function updateBanner(EventBannerRequest $request, Event $event): RedirectResponse
    {
        $path = $request->file('banner')->store('event-banners', 'public');

        if ($event->banner_path !== null) {
            Storage::disk('public')->delete($event->banner_path);
        }

        $event->update(['banner_path' => $path]);

        return redirect()
            ->route('admin.events.show', $event)
            ->with('status', 'Event banner updated.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        if ($event->vouchers()->exists()) {
            return redirect()
                ->route('admin.events.show', $event)
                ->withErrors(['event' => 'This event still has coupons. Delete or move them before deleting the event.']);
        }

        if ($event->banner_path !== null) {
            Storage::disk('public')->delete($event->banner_path);
        }

        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('status', 'Event deleted.');
    }
}
