<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApartadoAdminController extends Controller
{
    public function index(): Response
    {
        $query = Apartado::query()->withCount('detalles')->latest();

        if ($estado = (string) request()->query('estado', '')) {
            $query->where('estado', $estado);
        }

        if ($q = trim((string) request()->query('q', ''))) {
            $query->where(fn ($b) => $b
                ->where('codigo', 'like', "%{$q}%")
                ->orWhere('nombre_cliente', 'like', "%{$q}%")
                ->orWhere('telefono', 'like', "%{$q}%"));
        }

        $apartados = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Apartados/Index', [
            'apartados' => [
                'data' => $apartados->getCollection()->map(fn (Apartado $a) => [
                    'id' => $a->id,
                    'codigo' => $a->codigo,
                    'nombre_cliente' => $a->nombre_cliente,
                    'telefono' => $a->telefono,
                    'estado' => $a->estado,
                    'etiqueta_estado' => $a->etiquetaEstado(),
                    'clase_estado' => $a->claseEstado(),
                    'cantidad' => $a->detalles_count,
                    'subtotal' => $a->subtotal,
                    'fecha' => $a->created_at->format('d/m/Y H:i'),
                ]),
                'paginas' => $apartados->lastPage(),
                'pagina_actual' => $apartados->currentPage(),
            ],
            'total' => $apartados->total(),
            'estados' => collect(Apartado::ESTADOS)->map(fn ($etiqueta, $clave) => [
                'clave' => $clave,
                'etiqueta' => $etiqueta,
            ])->values(),
            'filtros' => [
                'estado' => request()->query('estado', ''),
                'q' => request()->query('q', ''),
            ],
        ]);
    }

    public function show(Apartado $apartado): Response
    {
        $apartado->load('detalles');

        return Inertia::render('Admin/Apartados/Detalle', [
            'apartado' => [
                'id' => $apartado->id,
                'codigo' => $apartado->codigo,
                'nombre_cliente' => $apartado->nombre_cliente,
                'telefono' => $apartado->telefono,
                'direccion' => $apartado->direccion,
                'nota' => $apartado->nota,
                'estado' => $apartado->estado,
                'etiqueta_estado' => $apartado->etiquetaEstado(),
                'clase_estado' => $apartado->claseEstado(),
                'subtotal' => $apartado->subtotal,
                'fecha' => $apartado->created_at->format('d/m/Y H:i'),
                'detalles' => $apartado->detalles->map(fn ($d) => [
                    'nombre' => $d->nombre_producto,
                    'cantidad' => $d->cantidad,
                    'precio' => $d->precio,
                    'subtotal' => $d->subtotal,
                ])->values(),
            ],
            'estados' => collect(Apartado::ESTADOS)->map(fn ($etiqueta, $clave) => [
                'clave' => $clave,
                'etiqueta' => $etiqueta,
            ])->values(),
        ]);
    }

    public function updateEstado(Request $request, Apartado $apartado): RedirectResponse
    {
        $datos = $request->validate([
            'estado' => ['required', 'string', 'in:'.implode(',', array_keys(Apartado::ESTADOS))],
        ]);

        $apartado->update($datos);

        return back()->with('ok', 'Estado del apartado actualizado.');
    }

    public function destroy(Apartado $apartado): RedirectResponse
    {
        $apartado->delete();

        return redirect()->route('admin.apartados.index')->with('ok', 'Apartado eliminado.');
    }
}