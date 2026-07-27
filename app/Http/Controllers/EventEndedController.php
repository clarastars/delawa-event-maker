<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class EventEndedController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('event-ended', [
            'locale' => $this->locale($request),
        ]);
    }

    private function locale(Request $request): string
    {
        return $request->query('lang') === 'en' || $request->input('lang') === 'en' ? 'en' : 'ar';
    }
}
