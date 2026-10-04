<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Un admin doit être connecté (et toujours admin en base) : invité → connexion, simple utilisateur → 403
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->guest(route('login'));
        }

        abort_unless(Auth::user()->is_admin, Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
