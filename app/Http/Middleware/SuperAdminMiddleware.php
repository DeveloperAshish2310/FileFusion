<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => 0, 'info' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login')->with('error', 'Please log in to access the Admin Console.');
        }

        if (!$user->isSuperAdmin()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => 0, 'info' => 'Access Denied: Super Admin privileges required.'], 403);
            }
            abort(403, 'Access Denied: You do not have Super Admin privileges.');
        }

        return $next($request);
    }
}
