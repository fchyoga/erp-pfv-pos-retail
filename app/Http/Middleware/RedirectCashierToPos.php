<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectCashierToPos
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->hasRole('kasir')) {
            if ($request->is('admin*') && !$request->is('admin/logout') && !$request->is('admin/login')) {
                return redirect('/pos');
            }
        }

        return $next($request);
    }
}
