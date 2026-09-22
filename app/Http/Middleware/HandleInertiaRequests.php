<?php

namespace App\Http\Middleware;

use App\Models\Ajuste;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'ziggy' => fn () => (new Ziggy)->toArray(),
            'flash' => [
                'ok' => fn () => $request->session()->get('ok'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'auth' => [
                'user' => $request->user()?->only(['id', 'nombre', 'email', 'es_admin']),
            ],
            'tienda' => [
                'nombre' => Ajuste::nombreTienda(),
                'whatsapp' => Ajuste::whatsapp(),
                'direccion' => Ajuste::direccion(),
                'moneda' => Ajuste::moneda(),
            ],
            'csrf_token' => fn () => $request->session()->token(),
        ];
    }
}
