<?php

namespace Khan\Forms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFormTestingEnvironment
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = config('forms.enabled', false);

        if (!$enabled) {
            abort(403, 'Forms testing dashboard is disabled in this environment.');
        }

        return $next($request);
    }
}
