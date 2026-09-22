<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampoCategoria;
use App\Models\Categoria;
use App\Models\ImagenProducto;
use App\Models\Producto;
use App\Models\ValorProducto;
use App\Services\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductoAdminController extends Controller
{
    public function index(): Response
    {
        $query = Producto::query()->with(['categoria.area', 'imagenes'])->latest();

        if ($q = trim((string) request()->query('q', ''))) {
            $query->where(fn ($b) => $b
                ->where('nombre', 'like', "%{$q}%")
                ->orWhere('codigo', 'like', "%{$q}%"));
        }

        if ($categoriaId = (int) request()->query('categoria', 0)) {
            $query->where('categoria_id', $categoriaId);
        }

        if (request()->has('agotados') && request()->boolean('agotados')) {
            $query->where('stock', 0);
        }

        $productos = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Productos/Index', [
            'productos' => [
                'data' => $productos->getCollection()->map(fn (Producto $p) => [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'codigo' => $p->codigo,
                    'marca' => $p->marca,
                    'unidad_de_medida' => $p->unidad_de_medida,
                    'disponibilidad' => $p->disponibilidad,
                    'categoria' => $p->categoria?->nombre ?? '—',
                    'area' => $p->categoria?->area?->nombre ?? '—',
                    'precio' => $p->precioVigente(),
                    'stock' => $p->stock,
                    'activo' => $p->activo,
                    'destacado' => $p->destacado,
                    'imagen' => $p->primeraImagen(),
                ]),
                'paginas' => $productos->lastPage(),
                'pagina_actual' => $productos->currentPage(),
            ],
            'total' => $productos->total(),
            'categorias' => Categoria::orderBy('nombre')->get(['id', 'nombre']),
            'filtros' => [
                'q' => request()->query('q', ''),
                'categoria' => request()->query('categoria', ''),
                'agotados' => request()->boolean('agotados'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Productos/Form', [
            'producto' => null,
            'categorias' => $this->categoriasConCampos(),
            'flash' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        $producto = new Producto($datos['producto']);
        $producto->slug = Producto::slugUnico($datos['producto']['nombre']);
        $producto->save();

        $this->guardarRelaciones($producto, $request);

        return redirect()->route('admin.productos.index')->with('ok', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto): Response
    {
        $producto->load(['imagenes', 'valores']);

        $valores = $producto->valores->mapWithKeys(fn (ValorProducto $v) => [(string) $v->campo_id => $v->valor])->all();

        return Inertia::render('Admin/Productos/Form', [
            'producto' => [
                'id' => $producto->id,
                'categoria_id' => $producto->categoria_id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'codigo' => $producto->codigo,
                'marca' => $producto->marca,
                'unidad_de_medida' => $producto->unidad_de_medida,
                'disponibilidad' => $producto->disponibilidad,
                'precio' => $producto->precio,
                'precio_oferta' => $producto->precio_oferta,
                'stock' => $producto->stock,
                'activo' => $producto->activo,
                'destacado' => $producto->destacado,
                'imagenes' => $producto->imagenes->map(fn (ImagenProducto $i) => [
                    'uid' => $i->id,
                    'ruta' => $i->ruta,
                    'url' => ImageOptimizer::urlPublica($i->ruta),
                ])->values(),
                'valores' => $valores,
            ],
            'categorias' => $this->categoriasConCampos(),
        ]);
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $datos = $this->validar($request);

        $producto->fill($datos['producto']);
        $producto->save();

        $this->guardarRelaciones($producto, $request);

        return redirect()->route('admin.productos.index')->with('ok', 'Producto actualizado.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $rutas = $producto->imagenes()->pluck('ruta')->all();

        foreach ($rutas as $ruta) {
            ImageOptimizer::olvidarCache($ruta);
            foreach (ImageOptimizer::ANCHOS as $ancho) {
                Storage::disk('public')->delete(ImageOptimizer::rutaVariante($ruta, $ancho));
            }
            Storage::disk('public')->delete($ruta);
        }

        $producto->delete();

        return back()->with('ok', 'Producto eliminado.');
    }

    public function uploadImagen(Request $request): JsonResponse
    {
        $request->validate([
            'imagen' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ], [
            'imagen.required' => 'Selecciona una imagen.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'Solo se admiten JPG, PNG o WebP.',
            'imagen.max' => 'La imagen no puede superar los 5 MB.',
        ]);

        $archivo = $request->file('imagen');

        $carpeta = 'productos/'.now()->format('Y/m');
        $nombre = Str::slug(pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME), '-').'-'.Str::random(8).'.'.$archivo->getClientOriginalExtension();
        $ruta = $archivo->storePubliclyAs($carpeta, $nombre, 'public');

        ImageOptimizer::optimizar($ruta, $archivo->getRealPath());

        return response()->json([
            'ruta' => $ruta,
            'url' => ImageOptimizer::urlPublica(Storage::disk('public')->url($ruta)),
        ]);
    }

    private function validar(Request $request): array
    {
        $categoria = Categoria::with('campos')->findOrFail((int) $request->input('categoria_id'));

        $reglasCampos = [];

        foreach ($categoria->campos as $campo) {
            $clave = "valores.{$campo->id}";
            $reglasCampos[$clave] = [$campo->obligatorio ? 'required' : 'nullable', 'string', 'max:150'];
        }

        $datos = $request->validate([
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'codigo' => ['nullable', 'string', 'max:60'],
            'marca' => ['nullable', 'string', 'max:80'],
            'unidad_de_medida' => ['required', 'string', 'max:30'],
            'disponibilidad' => ['required', 'string', 'in:'.implode(',', array_keys(Producto::DISPONIBILIDADES))],
            'precio' => ['required', 'numeric', 'min:0'],
            'precio_oferta' => ['nullable', 'numeric', 'min:0', 'lt:precio'],
            'stock' => ['required', 'integer', 'min:0'],
            'activo' => ['boolean'],
            'destacado' => ['boolean'],
            'imagenes' => ['array'],
            'imagenes.*' => ['string', 'max:255'],
            ...$reglasCampos,
        ], [
            'categoria_id.required' => 'Elige la categoria.',
            'nombre.required' => 'El nombre es obligatorio.',
            'precio.required' => 'El precio es obligatorio.',
            'precio_oferta.lt' => 'El precio de oferta debe ser menor al precio normal.',
            'stock.required' => 'Indica la cantidad en stock.',
            'stock.integer' => 'El stock debe ser un numero entero.',
        ]);

        return [
            'producto' => [
                'categoria_id' => $datos['categoria_id'],
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'codigo' => $datos['codigo'] ?? null,
                'marca' => $datos['marca'] ?? null,
                'unidad_de_medida' => $datos['unidad_de_medida'],
                'disponibilidad' => $datos['disponibilidad'],
                'precio' => $datos['precio'],
                'precio_oferta' => filled($datos['precio_oferta'] ?? null) ? $datos['precio_oferta'] : null,
                'stock' => $datos['stock'],
                'activo' => $request->boolean('activo'),
                'destacado' => $request->boolean('destacado'),
            ],
            'imagenes' => array_values(array_filter($datos['imagenes'] ?? [])),
            'valores' => $datos['valores'] ?? [],
            'campos' => $categoria->campos,
        ];
    }

    private function guardarRelaciones(Producto $producto, Request $request): void
    {
        $this->syncValores($producto, $request);
        $this->syncImagenes($producto, $request);
    }

    private function syncValores(Producto $producto, Request $request): void
    {
        $valores = (array) $request->input('valores', []);
        $campoIds = Categoria::with('campos')
            ->findOrFail($producto->categoria_id)
            ->campos
            ->pluck('id')
            ->all();

        $producto->valores()->delete();

        foreach ($campoIds as $campoId) {
            $valor = trim((string) ($valores[(string) $campoId] ?? ''));
            if ($valor !== '') {
                ValorProducto::create([
                    'producto_id' => $producto->id,
                    'campo_id' => $campoId,
                    'valor' => $valor,
                ]);
            }
        }
    }

    private function syncImagenes(Producto $producto, Request $request): void
    {
        $rutas = (array) $request->input('imagenes', []);
        $rutas = array_values(array_filter($rutas, fn ($r) => is_string($r) && $r !== ''));

        $existentes = $producto->imagenes()->pluck('ruta')->all();

        foreach ($existentes as $ruta) {
            if (! in_array($ruta, $rutas, true)) {
                ImageOptimizer::olvidarCache($ruta);
                foreach (ImageOptimizer::ANCHOS as $ancho) {
                    Storage::disk('public')->delete(ImageOptimizer::rutaVariante($ruta, $ancho));
                }
                Storage::disk('public')->delete($ruta);
            }
        }

        $producto->imagenes()->delete();

        foreach (array_values($rutas) as $i => $ruta) {
            ImagenProducto::create([
                'producto_id' => $producto->id,
                'ruta' => $ruta,
                'orden' => $i,
            ]);
        }
    }

    /**
     * @return array<int, array{
     *   id:int, nombre:string, area:string, campos: array<int, array<string, mixed>>
     * }>
     */
    private function categoriasConCampos(): array
    {
        return Categoria::with(['area', 'campos'])
            ->orderBy('nombre')
            ->get()
            ->map(fn (Categoria $categoria) => [
                'id' => $categoria->id,
                'nombre' => $categoria->nombre,
                'area' => $categoria->area?->nombre ?? '—',
                'campos' => $categoria->campos->map(fn (CampoCategoria $campo) => [
                    'id' => $campo->id,
                    'nombre' => $campo->nombre,
                    'etiqueta' => $campo->etiqueta ?? $campo->nombre,
                    'tipo' => $campo->tipo,
                    'unidad' => $campo->unidad,
                    'opciones' => $campo->opciones ?? [],
                    'obligatorio' => $campo->obligatorio,
                ])->values(),
            ])
            ->values()
            ->all();
    }
}
