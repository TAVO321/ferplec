<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
        'producto_id', 'proveedor_id', 'numero_lote', 'cantidad_inicial',
        'cantidad_disponible', 'costo_unitario', 'fecha_ingreso', 'nota',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_inicial' => 'integer',
            'cantidad_disponible' => 'integer',
            'costo_unitario' => 'decimal:2',
            'fecha_ingreso' => 'date',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoStock::class);
    }

    public function scopeConDisponible($query)
    {
        return $query->where('cantidad_disponible', '>', 0);
    }
}
