<?php

namespace App\Http\Controllers;

use App\Models\Apartado;
use App\Models\DetalleApartado;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ApartadoController extends Controller
{
    public function __construct(protected CartService $cart)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Apartado/Index', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
            'mensaje_whatsapp' => $this->mensajeWhatsapp(
                $this->cart->items(),
                $this->cart->total(),
            ),
            'whatsapp' => \App\Models\Ajuste::whatsapp(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($this->cart->estaVacio()) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }

        $datos = $request->validate([
            'nombre_cliente' => ['required', 'string', 'max:120'],
            'telefono' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'nota' => ['nullable', 'string', 'max:1000'],
        ], [
            'nombre_cliente.required' => 'Escribe tu nombre.',
            'telefono.required' => 'Escribe tu número de WhatsApp.',
            'telefono.regex' => 'El teléfono solo puede contener números.',
        ]);

        $items = $this->cart->items();

        try {
            $apartado = DB::transaction(function () use ($datos, $items) {
                $apartado = Apartado::create([
                    'codigo' => 'FP-'.strtoupper(Str::random(6)),
                    'nombre_cliente' => $datos['nombre_cliente'],
                    'telefono' => $datos['telefono'],
                    'direccion' => $datos['direccion'] ?? null,
                    'nota' => $datos['nota'] ?? null,
                    'estado' => 'apartado',
                    'subtotal' => $this->cart->total(),
                ]);

                foreach ($items as $item) {
                    DetalleApartado::create([
                        'apartado_id' => $apartado->id,
                        'producto_id' => $item['producto_id'],
                        'nombre_producto' => $item['nombre'],
                        'precio' => $item['precio'],
                        'cantidad' => $item['cantidad'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    \App\Models\Producto::whereKey($item['producto_id'])
                        ->decrement('stock', $item['cantidad']);
                }

                return $apartado;
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'No se pudo registrar tu apartado. Intentá de nuevo.');
        }

        $this->cart->limpiar();

        return redirect()->route('apartados.gracias', $apartado)->with('ok', '¡Apartado registrado!');
    }

    public function gracias(Apartado $apartado): Response
    {
        $apartado->load('detalles');

        return Inertia::render('Apartado/Gracias', [
            'apartado' => [
                'codigo' => $apartado->codigo,
                'nombre_cliente' => $apartado->nombre_cliente,
                'telefono' => $apartado->telefono,
                'estado' => $apartado->estado,
                'subtotal' => $apartado->subtotal,
                'detalles' => $apartado->detalles->map(fn (DetalleApartado $d) => [
                    'nombre' => $d->nombre_producto,
                    'cantidad' => $d->cantidad,
                    'precio' => $d->precio,
                    'subtotal' => $d->subtotal,
                ])->values(),
            ],
            'mensaje_whatsapp' => $this->mensajeApartado($apartado),
            'whatsapp' => \App\Models\Ajuste::whatsapp(),
        ]);
    }

    private function mensajeWhatsapp(array $items, string $total): string
    {
        $lineas = ['Hola FERPLEC, quiero apartar estos productos:'];

        foreach ($items as $item) {
            $lineas[] = "- {$item['cantidad']}x {$item['nombre']} (Bs {$item['precio']})";
        }

        $lineas[] = "Total: Bs {$total}";

        return implode("\n", $lineas);
    }

    private function mensajeApartado(Apartado $apartado): string
    {
        $lineas = ["Hola FERPLEC, quiero confirmar mi apartado {$apartado->codigo}:"];

        foreach ($apartado->detalles as $detalle) {
            $lineas[] = "- {$detalle->cantidad}x {$detalle->nombre_producto} (Bs {$detalle->precio})";
        }

        $lineas[] = "Total: Bs {$apartado->subtotal}";
        $lineas[] = "Nombre: {$apartado->nombre_cliente}";

        return implode("\n", $lineas);
    }
}