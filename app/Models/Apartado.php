<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apartado extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo', 'nombre_cliente', 'telefono', 'direccion', 'nota', 'estado', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
        ];
    }

    const ESTADOS = [
        'apartado' => 'Apartado',
        'confirmado' => 'Confirmado',
        'enviado' => 'Enviado',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    const COLORES_ESTADO = [
        'apartado' => 'bg-amber-100 text-amber-700',
        'confirmado' => 'bg-blue-100 text-blue-700',
        'enviado' => 'bg-violet-100 text-violet-700',
        'entregado' => 'bg-green-100 text-green-700',
        'cancelado' => 'bg-stone-100 text-stone-500',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleApartado::class, 'apartado_id');
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