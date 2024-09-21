<?php

namespace App\Http\Middleware\RouteMiddleware;

use App\Models\Comment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckCommentPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($request->isMethod('POST') && $request->route()->getName() == 'comments.add') {
            $policy = Gate::getPolicyFor(Comment::class);
            $film = $request->route('film');
            if (!$policy->create($user, $film)) {
                throw new AccessDeniedHttpException();
            }
        }

        if ($request->isMethod('PATCH') && $request->route()->getName() == 'comments.update') {
            if ($user->cant('update', $request->comment)) {
                throw new AccessDeniedHttpException();
            }
        }

        if ($request->isMethod('DELETE') && $request->route()->getName() == 'comments.delete') {
            if ($user->cant('delete', $request->comment)) {
                throw new AccessDeniedHttpException();
            }
        }

        return $next($request);
    }
}
