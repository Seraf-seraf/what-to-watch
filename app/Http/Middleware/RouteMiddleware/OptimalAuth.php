<?php

namespace App\Http\Middleware\RouteMiddleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

class OptimalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('sanctum')->check()) {
            Sanctum::actingAs(Auth::guard('sanctum')->user());
        }

        return $next($request);
    }
}
