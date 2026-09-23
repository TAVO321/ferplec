<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Categoria;
use App\Models\Producto;
use App\Services\ImageOptimizer;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    public function home(): Response
    {
        $areas = Area::where('activa', true)
            ->orderBy('orden')
            ->withCount(['categorias' => fn ($q) => $q->where('activa', true)])
            ->get()
            ->map(fn (Area $area) => [
                'id' => $area->id,
                'nombre' => $area->nombre,
                'slug' => $area->slug,
                'descripcion' => $area->descripcion,
                'icono' => $area->icono,
                'color' => $area->color,
                'cantidad_categorias' => $area->categorias_count,
            ]);

        $destacados = $this->transformarProductos(
            Producto::where('activo', true)->where('destacado', true)->with('categoria')->limit(4)->get(),
        );

        $nuevos = $this->transformarProductos(
            Producto::where('activo', true)->with('categoria')->latest()->limit(4)->get(),
        );

        return Inertia::render('Home', [
            'areas' => $areas,
            'destacados' => $destacados,
            'nuevos' => $nuevos,
        ]);
    }

    public function index(): Response
    {
        $query = Producto::query()->with(['categoria.area'])->where('activo', true);

        $areaSeleccionada = null;
        $categoriaSeleccionada = null;

        if ($areaId = (int) request()->query('area', 0)) {
            $query->whereHas('categoria', fn ($q) => $q->where('area_id', $areaId));
            $areaSeleccionada = Area::find($areaId);
        }

        if ($categoriaId = (int) request()->query('categoria', 0)) {
            $query->where('categoria_id', $categoriaId);
            $categoriaSeleccionada = Categoria::with('area')->find($categoriaId);
        }

        if ($q = trim((string) request()->query('q', ''))) {
            $query->where(fn ($b) => $b
                ->where('nombre', 'like', "%{$q}%")
                ->orWhere('descripcion', 'like', "%{$q}%")
                ->orWhere('codigo', 'like', "%{$q}%"));
        }

        $orden = (string) request()->query('orden', 'recientes');
        $orden = match ($orden) {
            'precio-asc' => 'precio_min_asc',
            'precio-desc' => 'precio_min_desc',
            'nombres' => 'nombre_asc',
            default => 'recientes',
        };

        switch ($orden) {
            case 'precio_min_asc':
                $query->orderByRaw('COALESCE(precio_oferta, precio) ASC');
                break;
            case 'precio_min_desc':
                $query->orderByRaw('COALESCE(precio_oferta, precio) DESC');
                break;
            case 'nombre_asc':
                $query->orderBy('nombre');
                break;
            default:
                $query->latest();
        }

        $productos = $query->paginate(12)->withQueryString();

        $areas = Area::where('activa', true)
            ->orderBy('orden')
            ->with(['categorias' => fn ($q) => $q->where('activa', true)->orderBy('orden')])
            ->get()
            ->map(fn (Area $area) => [
                'id' => $area->id,
                'nombre' => $area->nombre,
                'slug' => $area->slug,
                'categorias' => $area->categorias->map(fn (Categoria $c) => [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                ])->values(),
            ]);

        return Inertia::render('Catalogo/Index', [
            'areas' => $areas,
            'productos' => [
                'data' => $this->transformarProductos($productos->getCollection()),
                'paginas' => $productos->lastPage(),
                'pagina_actual' => $productos->currentPage(),
            ],
            'filtros' => [
                'q' => request()->query('q', ''),
                'area' => $areaId ?? 0,
                'categoria' => $categoriaId ?? 0,
                'orden' => request()->query('orden', 'recientes'),
                'area_nombre' => $areaSeleccionada?->nombre ?? null,
                'categoria_nombre' => $categoriaSeleccionada?->nombre ?? null,
            ],
        ]);
    }

    public function show(string $slug): Response
    {
        $producto = Producto::with(['categoria.area', 'imagenes'])->where('slug', $slug)->firstOrFail();

        if (! $producto->activo) {
            abort(404);
        }

        $relacionados = $this->transformarProductos(
            Producto::where('activo', true)
                ->where('categoria_id', $producto->categoria_id)
                ->where('id', '!=', $producto->id)
                ->with('categoria')
                ->limit(4)
                ->get(),
        );

        return Inertia::render('Catalogo/Detalle', [
            'producto' => $this->transformarProductoIndividual($producto),
            'relacionados' => $relacionados,
        ]);
    }

    private function transformarProductos($productos): array
    {
        return $productos->map(fn (Producto $producto) => [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'slug' => $producto->slug,
            'codigo' => $producto->codigo,
            'marca' => $producto->marca,
            'unidad_de_medida' => $producto->unidad_de_medida,
            'disponibilidad' => $producto->disponibilidad,
            'precio' => $producto->precioVigente(),
            'precio_anterior' => $producto->tieneOferta() ? $producto->precio : null,
            'imagen' => $this->imagenTarjeta($producto),
            'stock' => $producto->stock,
            'categoria' => $producto->categoria?->nombre ?? null,
            'area' => $producto->categoria?->area?->nombre ?? null,
        ])->values()->all();
    }

    private function transformarProductoIndividual(Producto $producto): array
    {
        $imagenes = $producto->imagenes->map(fn ($imagen) => [
            'uid' => $imagen->id,
            'full' => ImageOptimizer::urlVariante($imagen->url(), ImageOptimizer::ANCHOS[1]),
            'thumb' => ImageOptimizer::urlVariante($imagen->url(), ImageOptimizer::ANCHO_TARJETA),
        ])->values()->all();

        return [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'slug' => $producto->slug,
            'codigo' => $producto->codigo,
            'marca' => $producto->marca,
            'unidad_de_medida' => $producto->unidad_de_medida,
            'disponibilidad' => $producto->disponibilidad,
            'descripcion' => $producto->descripcion,
            'precio' => $producto->precioVigente(),
            'precio_anterior' => $producto->tieneOferta() ? $producto->precio : null,
            'estoque' => $producto->stock,
            'agotado' => $producto->stock <= 0 && $producto->disponibilidad === 'agotado',
            'imagenes' => $imagenes,
            'categoria' => [
                'id' => $producto->categoria->id ?? null,
                'nombre' => $producto->categoria->nombre ?? null,
                'area' => $producto->categoria->area->nombre ?? null,
            ],
            'atributos' => $producto->atributosResueltos(),
        ];
    }

    private function imagenTarjeta(Producto $producto): ?string
    {
        $imagen = $producto->primeraImagen();

        return $imagen ? ImageOptimizer::urlVariante($imagen, ImageOptimizer::ANCHO_TARJETA) : null;
    }
}
