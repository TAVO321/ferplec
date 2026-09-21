<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartado;
use App\Models\Categoria;
use App\Models\Producto;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function dashboard(): Response
    {
        $apartados = Apartado::select(
            'estado',
            'created_at',
            'subtotal',
        );

        return Inertia::render('Admin/Dashboard', [
            'estadisticas' => [
                'productos' => Producto::count(),
                'productos_activos' => Producto::where('activo', true)->count(),
                'productos_sin_stock' => Producto::where('stock', 0)->count(),
                'stock_bajo' => Producto::where('stock', '>', 0)->where('stock', '<=', 5)->count(),
                'categorias' => Categoria::count(),
                'apartados_pendientes' => Apartado::whereIn('estado', ['apartado', 'confirmado'])->count(),
                'apartados_entregados' => Apartado::where('estado', 'entregado')->count(),
                'ventas' => (float) (clone $apartados)->where('estado', '!=', 'cancelado')->sum('subtotal'),
                'apartados_mes' => (clone $apartados)->where('created_at', '>=', now()->startOfMonth())->count(),
            ],
            'ultimos_apartados' => Apartado::withCount('detalles')
                ->orderByDesc('created_at')
                ->limit(8)
                ->get()
                ->map(fn (Apartado $a) => [
                    'id' => $a->id,
                    'codigo' => $a->codigo,
                    'nombre_cliente' => $a->nombre_cliente,
                    'estado' => $a->estado,
                    'etiqueta_estado' => $a->etiquetaEstado(),
                    'clase_estado' => $a->claseEstado(),
                    'subtotal' => $a->subtotal,
                    'fecha' => $a->created_at->diffForHumans(),
                    'cantidad' => $a->detalles_count,
                ]),
            'productos_descargados' => Producto::where('activo', true)
                ->orderBy('stock')
                ->limit(8)
                ->get()
                ->map(fn (Producto $p) => [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'stock' => $p->stock,
                ]),
        ]);
    }
}