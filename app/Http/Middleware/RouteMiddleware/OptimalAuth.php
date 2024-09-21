<?php

namespace App\Http\Middleware\RouteMiddleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OptimalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('sanctum')->check()) {
            $request->attributes->set('user', Auth::guard('sanctum')->user());
        } else {
            $request->attributes->set('user', null);
        }

        return $next($request);
    }
}
