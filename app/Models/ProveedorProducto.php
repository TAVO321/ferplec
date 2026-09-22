<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProveedorProducto extends Pivot
{
    protected $table = 'proveedor_producto';

    protected $fillable = [
        'proveedor_id', 'producto_id', 'costo', 'tiempo_entrega_dias', 'nota',
    ];

    protected function casts(): array
    {
        return [
            'costo' => 'decimal:2',
            'tiempo_entrega_dias' => 'integer',
        ];
    }
}
