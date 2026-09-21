<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleApartado extends Model
{
    protected $table = 'detalle_apartados';

    protected $fillable = ['apartado_id', 'producto_id', 'nombre_producto', 'precio', 'cantidad', 'subtotal'];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'cantidad' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }

    public function apartado(): BelongsTo
    {
        return $this->belongsTo(Apartado::class, 'apartado_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}