<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Campo personalizado de una categoría. Cada área (ferretería, electricidad,
 * plomería) tiene datos distintos, por eso los atributos se definen por
 * categoría y se rellenan por producto en `valores_producto`.
 */
class CampoCategoria extends Model
{
    use HasFactory;

    protected $table = 'campos_categoria';

    protected $fillable = ['categoria_id', 'nombre', 'etiqueta', 'tipo', 'unidad', 'opciones', 'obligatorio', 'orden'];

    protected function casts(): array
    {
        return [
            'opciones' => 'array',
            'obligatorio' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}