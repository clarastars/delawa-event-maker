<?php

namespace App\Http\Middleware;

use App\Models\Event;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEventIsOpen
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $event = $request->route('event');

        if ($event instanceof Event && $event->isClosed()) {
            $locale = $request->query('lang') === 'en' || $request->input('lang') === 'en' ? 'en' : 'ar';

            return response()->view('event-ended', [
                'locale' => $locale,
            ]);
        }

        return $next($request);
    }
}
