<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ajuste;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AjusteAdminController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Ajustes/Index', [
            'ajustes' => [
                'nombre_tienda' => Ajuste::obtener('nombre_tienda', 'FERPLEC'),
                'whatsapp' => Ajuste::obtener('whatsapp', ''),
                'direccion' => Ajuste::obtener('direccion', ''),
                'moneda' => 'Bs',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre_tienda' => ['required', 'string', 'max:80'],
            'whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]*$/'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ], [
            'nombre_tienda.required' => 'El nombre de la tienda es obligatorio.',
            'whatsapp.regex' => 'El número de WhatsApp solo puede contener números.',
        ]);

        foreach ($datos as $clave => $valor) {
            Ajuste::asignar($clave, $valor);
        }

        return back()->with('ok', 'Ajustes guardados.');
    }
}