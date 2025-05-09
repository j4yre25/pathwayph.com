<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
  public function handle($request, Closure $next, $guard = null)
{
    // Skip verification check for the /email/verify route
    if ($request->routeIs('verification.notice')) {
        return $next($request);
    }



    if (! $request->user() || ! $request->user()->hasVerifiedEmail()) {
        return $request->expectsJson()
                    ? abort(403, 'Your email address is not verified.')
                    : redirect()->route('verification.notice');
    }

    return $next($request);
}

}