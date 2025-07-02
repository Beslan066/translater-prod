<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Translater
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
        if (auth()->user()->role !== 3) {
            return redirect('/');
        }

        return $next($request);
    }
}
