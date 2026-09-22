<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoEspecial extends Model
{
    use HasFactory;

    protected $table = 'pedidos_especiales';

    protected $fillable = [
        'codigo', 'cliente_id', 'nombre_cliente', 'telefono', 'direccion', 'ubicacion',
        'producto_solicitado', 'cantidad', 'unidad_de_medida', 'fecha_requerida',
        'estado', 'subtotal', 'proveedor_id', 'nota',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'subtotal' => 'decimal:2',
            'fecha_requerida' => 'date',
        ];
    }

    const ESTADOS = [
        'solicitado' => 'Solicitado',
        'cotizando' => 'Cotizando',
        'cotizado' => 'Cotizado',
        'confirmado' => 'Confirmado',
        'en_traslado' => 'En traslado',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    const COLORES_ESTADO = [
        'solicitado' => 'bg-amber-100 text-amber-700',
        'cotizando' => 'bg-blue-100 text-blue-700',
        'cotizado' => 'bg-violet-100 text-violet-700',
        'confirmado' => 'bg-indigo-100 text-indigo-700',
        'en_traslado' => 'bg-orange-100 text-orange-700',
        'entregado' => 'bg-green-100 text-green-700',
        'cancelado' => 'bg-stone-100 text-stone-500',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function etiquetaEstado(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    public function claseEstado(): string
    {
        return self::COLORES_ESTADO[$this->estado] ?? 'bg-stone-100 text-stone-500';
    }
}
