<?php

namespace App\Models;

use App\Services\ImageOptimizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImagenProducto extends Model
{
    use HasFactory;

    protected $table = 'imagenes_producto';

    protected $fillable = ['producto_id', 'ruta', 'orden'];

    public $timestamps = false;

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function url(): string
    {
        return ImageOptimizer::publicUrl($this->ruta) ?? '';
    }

    public function urlVariante(int $ancho): string
    {
        return ImageOptimizer::urlVariante($this->url(), $ancho);
    }
}