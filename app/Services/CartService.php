<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Carrito de compras guardado en sesión (sin necesidad de cuenta).
 */
class CartService
{
    public function __construct(protected Request $request)
    {
    }

    public function agregar(int $productoId, int $cantidad = 1): void
    {
        $carrito = $this->obtener();
        $cantidad = max(1, $cantidad);

        $carrito[$productoId] = ($carrito[$productoId] ?? 0) + $cantidad;

        $this->guardar($carrito);
    }

    public function actualizar(int $productoId, int $cantidad): void
    {
        $carrito = $this->obtener();

        if ($cantidad > 0) {
            $carrito[$productoId] = $cantidad;
        } else {
            unset($carrito[$productoId]);
        }

        $this->guardar($carrito);
    }

    public function quitar(int $productoId): void
    {
        $carrito = $this->obtener();
        unset($carrito[$productoId]);
        $this->guardar($carrito);
    }

    public function limpiar(): void
    {
        $this->guardar([]);
    }

    /**
     * Líneas del carrito con datos frescos del producto.
     *
     * @return array<int, array<string, mixed>>
     */
    public function items(): array
    {
        $cache = $this->productosEnCache();

        return $cache->values()
            ->map(fn (Producto $producto, int $i) => [
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre,
                'slug' => $producto->slug,
                'precio' => $producto->precioVigente(),
                'precio_anterior' => $producto->tieneOferta() ? $producto->precio : null,
                'cantidad' => $this->cantidadDe($producto->id),
                'subtotal' => number_format((float) $producto->precioVigente() * $this->cantidadDe($producto->id), 2, '.', ''),
                'imagen' => $producto->primeraImagen(),
                'stock' => $producto->stock,
            ])
            ->all();
    }

    public function total(): string
    {
        $total = 0;

        foreach ($this->products() as $producto) {
            $total += (float) $producto->precioVigente() * $this->cantidadDe($producto->id);
        }

        return number_format($total, 2, '.', '');
    }

    public function contar(): int
    {
        return array_sum($this->obtener());
    }

    public function cantidadDe(int $productoId): int
    {
        return (int) ($this->obtener()[$productoId] ?? 0);
    }

    public function estaVacio(): bool
    {
        return $this->contar() === 0;
    }

    public function productoIds(): array
    {
        return array_keys($this->obtener());
    }

    /**
     * @return Collection<int, Producto>
     */
    public function products(): Collection
    {
        return $this->productosEnCache();
    }

    private function productosEnCache(): Collection
    {
        $ids = collect($this->productoIds())->filter()->unique()->values()->all();

        if ($ids === []) {
            return collect();
        }

        return Producto::whereIn('id', $ids)
            ->where('activo', true)
            ->with('categoria')
            ->get()
            ->sortBy(fn (Producto $p) => array_search($p->id, $ids, true))
            ->values();
    }

    private function obtener(): array
    {
        return (array) $this->request->session()->get('carrito', []);
    }

    private function guardar(array $carrito): void
    {
        $this->request->session()->put('carrito', $carrito);
    }
}