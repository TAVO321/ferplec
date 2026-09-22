<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LoteAdminController extends Controller
{
    public function index(): Response
    {
        $lotes = Lote::with(['producto', 'proveedor'])
            ->latest('fecha_ingreso')
            ->paginate(20)
            ->through(fn (Lote $lote) => [
                'id' => $lote->id,
                'producto' => $lote->producto?->nombre,
                'proveedor' => $lote->proveedor?->nombre ?? '—',
                'numero_lote' => $lote->numero_lote ?? '—',
                'cantidad_inicial' => $lote->cantidad_inicial,
                'cantidad_disponible' => $lote->cantidad_disponible,
                'costo_unitario' => $lote->costo_unitario,
                'fecha_ingreso' => $lote->fecha_ingreso->format('d/m/Y'),
            ]);

        return Inertia::render('Admin/Lotes/Index', [
            'lotes' => [
                'data' => $lotes->getCollection(),
                'paginas' => $lotes->lastPage(),
                'pagina_actual' => $lotes->currentPage(),
            ],
            'productos' => Producto::orderBy('nombre')->get(['id', 'nombre']),
            'proveedores' => Proveedor::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'numero_lote' => ['nullable', 'string', 'max:60'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'costo_unitario' => ['required', 'numeric', 'min:0'],
            'fecha_ingreso' => ['required', 'date'],
            'nota' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($datos) {
            $lote = Lote::create([
                'producto_id' => $datos['producto_id'],
                'proveedor_id' => $datos['proveedor_id'] ?? null,
                'numero_lote' => $datos['numero_lote'] ?? null,
                'cantidad_inicial' => $datos['cantidad'],
                'cantidad_disponible' => $datos['cantidad'],
                'costo_unitario' => $datos['costo_unitario'],
                'fecha_ingreso' => $datos['fecha_ingreso'],
                'nota' => $datos['nota'] ?? null,
            ]);

            $producto = Producto::lockForUpdate()->find($datos['producto_id']);
            $stockAnterior = $producto->stock;
            $producto->increment('stock', $datos['cantidad']);

            MovimientoStock::create([
                'producto_id' => $producto->id,
                'lote_id' => $lote->id,
                'tipo' => 'entrada',
                'cantidad' => $datos['cantidad'],
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $producto->fresh()->stock,
                'referencia_type' => Lote::class,
                'referencia_id' => $lote->id,
                'usuario_id' => $request->user()?->id,
                'nota' => 'Ingreso de lote '.($datos['numero_lote'] ?? '#'.$lote->id),
            ]);
        });

        return back()->with('ok', 'Lote registrado y stock actualizado.');
    }
}
