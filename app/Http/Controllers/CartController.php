<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(protected CartService $cart)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Cart/Index', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'producto_id' => ['required', 'integer'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:99'],
        ], [
            'producto_id.required' => 'El producto es obligatorio.',
            'cantidad.required' => 'Indica la cantidad.',
        ]);

        $producto = Producto::where('id', $datos['producto_id'])->where('activo', true)->first();

        if (! $producto) {
            throw ValidationException::withMessages(['producto_id' => 'El producto no existe.']);
        }

        $cantidadTotal = $this->cart->cantidadDe($producto->id) + (int) $datos['cantidad'];

        if ($producto->stock <= 0) {
            throw ValidationException::withMessages(['cantidad' => 'Este producto está agotado.']);
        }

        if ($cantidadTotal > $producto->stock) {
            throw ValidationException::withMessages([
                'cantidad' => "Solo hay {$producto->stock} unidad(es) disponibles.",
            ]);
        }

        $this->cart->agregar($producto->id, (int) $datos['cantidad']);

        return back()->with('ok', 'Producto agregado al carrito.');
    }

    public function update(Request $request, int $productoId): RedirectResponse
    {
        $datos = $request->validate([
            'cantidad' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $producto = Producto::find($productoId);

        if ($producto && (int) $datos['cantidad'] > $producto->stock) {
            throw ValidationException::withMessages([
                'cantidad' => "Solo hay {$producto->stock} unidad(es) disponibles.",
            ]);
        }

        $this->cart->actualizar($productoId, (int) $datos['cantidad']);

        if ((int) $datos['cantidad'] === 0) {
            Session::flash('ok', 'Producto quitado del carrito.');
        }

        return back();
    }

    public function destroy(int $productoId): RedirectResponse
    {
        $this->cart->quitar($productoId);

        return back()->with('ok', 'Producto quitado del carrito.');
    }
}