<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Un admin doit être connecté (et toujours admin en base), sinon redirection vers la connexion
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::user()?->is_admin) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
