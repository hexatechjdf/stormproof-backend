<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  // This allows us to pass multiple role arguments
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect('login');
        }

        // Check if the user's role is in the list of allowed roles
        $user = Auth::user();
        if (!in_array($user->role, $roles)) {
            // Redirect or abort if the role does not match
            abort(403, 'Unauthorized Action');
        }

        return $next($request);
    }
}
