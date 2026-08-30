<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureScannerPinIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() || $request->session()->get('scanner_pin_verified') === true) {
            return $next($request);
        }

        return redirect()->route('admin.scan.pin');
    }
}
