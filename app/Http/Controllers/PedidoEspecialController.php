<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\PedidoEspecial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PedidoEspecialController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('PedidoEspecial/Form');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre_cliente' => ['required', 'string', 'max:120'],
            'telefono' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'producto_solicitado' => ['required', 'string', 'max:255'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'unidad_de_medida' => ['required', 'string', 'max:30'],
            'fecha_requerida' => ['nullable', 'date', 'after_or_equal:today'],
            'nota' => ['nullable', 'string', 'max:1000'],
        ]);

        $pedido = PedidoEspecial::create([
            'codigo' => 'PE-'.strtoupper(Str::random(6)),
            'nombre_cliente' => $datos['nombre_cliente'],
            'telefono' => preg_replace('/[^0-9]/', '', $datos['telefono']),
            'direccion' => $datos['direccion'] ?? null,
            'ubicacion' => $datos['ubicacion'] ?? null,
            'producto_solicitado' => $datos['producto_solicitado'],
            'cantidad' => $datos['cantidad'],
            'unidad_de_medida' => $datos['unidad_de_medida'],
            'fecha_requerida' => $datos['fecha_requerida'] ?? null,
            'estado' => 'solicitado',
            'nota' => $datos['nota'] ?? null,
        ]);

        return redirect()->route('pedidos_especiales.gracias', $pedido)
            ->with('ok', 'Tu solicitud fue registrada. Te contactaremos por WhatsApp.');
    }

    public function gracias(PedidoEspecial $pedido): Response
    {
        return Inertia::render('PedidoEspecial/Gracias', [
            'pedido' => [
                'codigo' => $pedido->codigo,
                'producto_solicitado' => $pedido->producto_solicitado,
                'cantidad' => $pedido->cantidad,
                'unidad_de_medida' => $pedido->unidad_de_medida,
                'estado' => $pedido->estado,
            ],
            'mensaje_whatsapp' => $this->mensajeWhatsapp($pedido),
            'whatsapp' => Ajuste::whatsapp(),
        ]);
    }

    private function mensajeWhatsapp(PedidoEspecial $pedido): string
    {
        $lineas = [
            'Hola FERPLEC, quiero solicitar un pedido especial:',
            "Codigo: {$pedido->codigo}",
            "Producto: {$pedido->producto_solicitado}",
            "Cantidad: {$pedido->cantidad} {$pedido->unidad_de_medida}",
            "Nombre: {$pedido->nombre_cliente}",
            "Telefono: {$pedido->telefono}",
        ];

        if ($pedido->direccion) {
            $lineas[] = "Direccion: {$pedido->direccion}";
        }
        if ($pedido->fecha_requerida) {
            $lineas[] = "Fecha requerida: {$pedido->fecha_requerida->format('d/m/Y')}";
        }

        $lineas[] = 'Confirmo que pagare al contado y adelantado una vez se me cotice.';

        return implode("\n", $lineas);
    }
}
