<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Allow the request through only when the authenticated user holds the
     * admin role. Guests are redirected to the login page by the `auth`
     * middleware that runs before this one.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isAdmin()) {
            abort(Response::HTTP_FORBIDDEN, 'This area is restricted to StudyNest administrators.');
        }

        return $next($request);
    }
}
