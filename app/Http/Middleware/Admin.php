<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем аутентификацию
        if (!auth()->check()) {
            return redirect()->route('login'); // или '/login'
        }

        // Проверяем роль
        if (auth()->user()->role !== 1) {
            return redirect('/'); // или abort(403)
        }

        return $next($request);
    }
}
