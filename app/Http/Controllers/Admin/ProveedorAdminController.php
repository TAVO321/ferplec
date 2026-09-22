<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProveedorAdminController extends Controller
{
    public function index(): Response
    {
        $proveedores = Proveedor::orderBy('nombre')
            ->withCount('lotes')
            ->get()
            ->map(fn (Proveedor $p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'contacto' => $p->contacto,
                'telefono' => $p->telefono,
                'email' => $p->email,
                'materiales' => $p->materiales,
                'activo' => $p->activo,
                'lotes_count' => $p->lotes_count,
            ]);

        return Inertia::render('Admin/Proveedores/Index', [
            'proveedores' => $proveedores,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'contacto' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'materiales' => ['nullable', 'string', 'max:500'],
        ]);

        Proveedor::create($datos + ['activo' => true]);

        return back()->with('ok', 'Proveedor creado.');
    }

    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'contacto' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'materiales' => ['nullable', 'string', 'max:500'],
            'activo' => ['boolean'],
        ]);

        $proveedor->fill($datos);
        $proveedor->activo = $request->boolean('activo', $proveedor->activo);
        $proveedor->save();

        return back()->with('ok', 'Proveedor actualizado.');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        $proveedor->delete();

        return back()->with('ok', 'Proveedor eliminado.');
    }
}
