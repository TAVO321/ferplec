<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoStock extends Model
{
    use HasFactory;

    protected $table = 'movimientos_stock';

    protected $fillable = [
        'producto_id', 'lote_id', 'tipo', 'cantidad', 'stock_anterior',
        'stock_nuevo', 'referencia_type', 'referencia_id', 'usuario_id', 'nota',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'stock_anterior' => 'integer',
            'stock_nuevo' => 'integer',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function referencia()
    {
        return $this->morphTo();
    }
}
