<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'slug', 'descripcion', 'icono', 'color', 'orden', 'activa'];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'activa' => 'boolean',
        ];
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class);
    }
}