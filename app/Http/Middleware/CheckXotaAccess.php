<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckXotaAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->dashrash) {
            return $next($request);
        }

        abort(403, 'AQUI É SEGURO AMOR :(');
    }
}
