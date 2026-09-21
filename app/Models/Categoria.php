<?php

namespace App\Models;

use App\Services\ImageOptimizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = ['area_id', 'nombre', 'slug', 'descripcion', 'imagen', 'orden', 'activa'];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'activa' => 'boolean',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function campos(): HasMany
    {
        return $this->hasMany(CampoCategoria::class, 'categoria_id')->orderBy('orden');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    /**
     * URL pública de la imagen de la categoría.
     */
    protected function getImagenUrlAttribute(): ?string
    {
        return ImageOptimizer::publicUrl($this->attributes['imagen'] ?? null);
    }
}