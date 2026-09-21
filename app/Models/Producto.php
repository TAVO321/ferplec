<?php

namespace App\Models;

use App\Services\ImageOptimizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'categoria_id', 'nombre', 'slug', 'descripcion', 'codigo',
        'precio', 'precio_oferta', 'stock', 'activo', 'destacado',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'precio_oferta' => 'decimal:2',
            'stock' => 'integer',
            'activo' => 'boolean',
            'destacado' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Producto $producto) {
            if (empty($producto->slug)) {
                $producto->slug = static::slugUnico($producto->nombre);
            }
        });
    }

    public static function slugUnico(string $nombre): string
    {
        $base = \Illuminate\Support\Str::slug($nombre);
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(ImagenProducto::class, 'producto_id')->orderBy('orden');
    }

    public function valores(): HasMany
    {
        return $this->hasMany(ValorProducto::class, 'producto_id');
    }

    /**
     * URL de la primera imagen (o null).
     */
    public function primeraImagen(): ?string
    {
        $primera = $this->imagenes()->orderBy('orden')->value('ruta');

        return $primera ? ImageOptimizer::publicUrl($primera) : null;
    }

    public function precioVigente(): string
    {
        return $this->precio_oferta !== null && $this->precio_oferta < $this->precio
            ? $this->precio_oferta
            : $this->precio;
    }

    public function tieneOferta(): bool
    {
        return $this->precio_oferta !== null
            && $this->precio_oferta > 0
            && $this->precio_oferta < $this->precio;
    }

    /**
     * Atributos personalizados resueltos como lista de pares etiqueta/valor.
     *
     * @return array<int, array{etiqueta: string, valor: string, unidad: ?string}>
     */
    public function atributosResueltos(): array
    {
        return $this->valores()
            ->with('campo')
            ->get()
            ->filter(fn (ValorProducto $v) => $v->campo !== null && filled($v->valor))
            ->sortBy(fn (ValorProducto $v) => $v->campo->orden)
            ->map(fn (ValorProducto $v) => [
                'etiqueta' => $v->campo->etiqueta ?? $v->campo->nombre,
                'valor' => $v->valor,
                'unidad' => $v->campo->unidad,
            ])
            ->values()
            ->all();
    }
}