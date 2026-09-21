<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValorProducto extends Model
{
    protected $table = 'valores_producto';

    protected $fillable = ['producto_id', 'campo_id', 'valor'];

    public $timestamps = false;

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function campo(): BelongsTo
    {
        return $this->belongsTo(CampoCategoria::class, 'campo_id');
    }
}