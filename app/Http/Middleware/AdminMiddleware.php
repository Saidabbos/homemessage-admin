<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated with admin guard
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Avval login qiling');
        }

        // Check if user has admin or dispatcher role
        $user = Auth::guard('admin')->user();
        if (!$user->hasAnyRole(['admin', 'dispatcher'])) {
            Auth::guard('admin')->logout();
            abort(403, 'Sizda bu sahifaga kirish ruxsati yo\'q');
        }

        return $next($request);
    }
}
