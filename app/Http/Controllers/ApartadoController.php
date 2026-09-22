<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\Apartado;
use App\Models\DetalleApartado;
use App\Models\Lote;
use App\Models\MovimientoStock;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ApartadoController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Apartado/Index', [
            'whatsapp' => Ajuste::whatsapp(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre_cliente' => ['required', 'string', 'max:120'],
            'telefono' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'nota' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:99'],
        ], [
            'nombre_cliente.required' => 'Escribe tu nombre.',
            'telefono.required' => 'Escribe tu numero de WhatsApp.',
            'telefono.regex' => 'El telefono solo puede contener numeros.',
            'items.required' => 'Tu carrito esta vacio.',
            'items.min' => 'Tu carrito esta vacio.',
        ]);

        $items = $datos['items'];
        $telefono = preg_replace('/[^0-9]/', '', $datos['telefono']);

        try {
            $apartado = DB::transaction(function () use ($datos, $items, $telefono) {
                $productos = Producto::whereIn('id', array_column($items, 'producto_id'))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $lineas = [];
                $subtotal = 0;

                foreach ($items as $item) {
                    $producto = $productos->get($item['producto_id']);

                    if (! $producto) {
                        throw new \RuntimeException('El producto ya no existe.');
                    }

                    if ($producto->disponibilidad === 'agotado' && $producto->stock < $item['cantidad']) {
                        throw new \RuntimeException(
                            "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock} {$producto->unidad_de_medida}"
                        );
                    }

                    if ($producto->disponibilidad !== 'bajo_pedido' && $producto->stock < $item['cantidad']) {
                        throw new \RuntimeException(
                            "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock} {$producto->unidad_de_medida}"
                        );
                    }

                    $precio = $producto->precioVigente();
                    $cantidad = $item['cantidad'];
                    $subtotalLinea = (float) $precio * $cantidad;
                    $subtotal += $subtotalLinea;

                    $lineas[] = [
                        'producto' => $producto,
                        'cantidad' => $cantidad,
                        'precio' => $precio,
                        'subtotal' => $subtotalLinea,
                    ];
                }

                $apartado = Apartado::create([
                    'codigo' => 'FP-'.strtoupper(Str::random(6)),
                    'nombre_cliente' => $datos['nombre_cliente'],
                    'telefono' => $telefono,
                    'direccion' => $datos['direccion'] ?? null,
                    'ubicacion' => $datos['ubicacion'] ?? null,
                    'nota' => $datos['nota'] ?? null,
                    'estado' => 'apartado',
                    'subtotal' => number_format($subtotal, 2, '.', ''),
                ]);

                foreach ($lineas as $linea) {
                    $producto = $linea['producto'];
                    $cantidad = $linea['cantidad'];

                    if ($producto->disponibilidad === 'bajo_pedido') {
                        DetalleApartado::create([
                            'apartado_id' => $apartado->id,
                            'producto_id' => $producto->id,
                            'lote_id' => null,
                            'nombre_producto' => $producto->nombre,
                            'precio' => $linea['precio'],
                            'cantidad' => $cantidad,
                            'subtotal' => number_format($linea['subtotal'], 2, '.', ''),
                        ]);

                        continue;
                    }

                    $cantidadPendiente = $cantidad;
                    $lotes = Lote::where('producto_id', $producto->id)
                        ->where('cantidad_disponible', '>', 0)
                        ->orderBy('fecha_ingreso')
                        ->lockForUpdate()
                        ->get();

                    foreach ($lotes as $lote) {
                        if ($cantidadPendiente <= 0) {
                            break;
                        }

                        $descontar = min($cantidadPendiente, $lote->cantidad_disponible);
                        $lote->decrement('cantidad_disponible', $descontar);
                        $cantidadPendiente -= $descontar;

                        DetalleApartado::create([
                            'apartado_id' => $apartado->id,
                            'producto_id' => $producto->id,
                            'lote_id' => $lote->id,
                            'nombre_producto' => $producto->nombre,
                            'precio' => $linea['precio'],
                            'cantidad' => $descontar,
                            'subtotal' => number_format((float) $linea['precio'] * $descontar, 2, '.', ''),
                        ]);
                    }

                    $stockAnterior = $producto->stock;
                    $producto->decrement('stock', $cantidad);

                    MovimientoStock::create([
                        'producto_id' => $producto->id,
                        'lote_id' => null,
                        'tipo' => 'apartado',
                        'cantidad' => -$cantidad,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $producto->fresh()->stock,
                        'referencia_type' => Apartado::class,
                        'referencia_id' => $apartado->id,
                        'nota' => 'Apartado '.$apartado->codigo,
                    ]);
                }

                return $apartado;
            });
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages(['items' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'No se pudo registrar tu apartado. Intenta de nuevo.');
        }

        return redirect()->route('apartados.gracias', $apartado)->with('ok', 'Apartado registrado!');
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
            'whatsapp' => Ajuste::whatsapp(),
        ]);
    }

    private function mensajeApartado(Apartado $apartado): string
    {
        $moneda = Ajuste::moneda();
        $lineas = ["Hola FERPLEC, quiero confirmar mi apartado {$apartado->codigo}:"];

        foreach ($apartado->detalles as $detalle) {
            $lineas[] = "- {$detalle->cantidad}x {$detalle->nombre_producto} ({$moneda} {$detalle->precio})";
        }

        $lineas[] = "Total: {$moneda} {$apartado->subtotal}";
        $lineas[] = "Nombre: {$apartado->nombre_cliente}";
        $lineas[] = "Telefono: {$apartado->telefono}";
        if ($apartado->direccion) {
            $lineas[] = "Direccion: {$apartado->direccion}";
        }
        $lineas[] = 'Confirmo que pagare al contado y adelantado.';

        return implode("\n", $lineas);
    }
}
