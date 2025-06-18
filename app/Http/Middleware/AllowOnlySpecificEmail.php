<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowOnlySpecificEmail
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedEmail = 'vanessamorada@gmail.com';

        if (!auth()->check() || auth()->user()->email !== $allowedEmail) {
            abort(403, 'Acesso negado');
        }

        return $next($request);
    }
}
