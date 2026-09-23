<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'estadisticas' => [
                'productos' => Producto::count(),
                'productos_activos' => Producto::where('activo', true)->count(),
                'productos_sin_stock' => Producto::where('stock', 0)->count(),
                'stock_bajo' => Producto::where('stock', '>', 0)->where('stock', '<=', 5)->count(),
                'categorias' => Categoria::count(),
            ],
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
