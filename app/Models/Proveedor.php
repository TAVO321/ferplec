<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'nombre', 'contacto', 'telefono', 'email', 'direccion', 'materiales', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function pedidosEspeciales(): HasMany
    {
        return $this->hasMany(PedidoEspecial::class);
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'proveedor_producto')
            ->withPivot(['costo', 'tiempo_entrega_dias', 'nota'])
            ->withTimestamps();
    }
}
