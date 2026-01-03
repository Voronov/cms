<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreserveSystemConfig
{
    public function handle(Request $request, Closure $next)
    {
        // This middleware runs AFTER transform middlewares
        // Just pass through - the system_config should already be in the request
        return $next($request);
    }
}
