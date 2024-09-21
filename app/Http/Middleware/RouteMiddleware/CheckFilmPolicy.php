<?php

namespace App\Http\Middleware\RouteMiddleware;

use App\Models\Film;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckFilmPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeNames = ['films.update', 'films.add', 'films.delete'];
        $currentRouteName = $request->route()->getName();

        if (in_array($currentRouteName, $routeNames)) {
            if ($user->cant('moderate', Film::class)) {
                throw new AccessDeniedHttpException();
            }
        }

        return $next($request);
    }
}
