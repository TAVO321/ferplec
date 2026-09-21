<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->esAdministrador() !== true) {
            if (! $request->user()) {
                return redirect()->guest(route('admin.login'));
            }

            abort(403, 'No tienes permisos para entrar a esta sección.');
        }

        return $next($request);
    }
}