<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\CampoCategoria;
use App\Models\Categoria;
use App\Services\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CatalogAdminController extends Controller
{
    public function index(): Response
    {
        $areas = Area::with(['categorias.campos'])
            ->orderBy('orden')
            ->get()
            ->map(fn (Area $area) => [
                'id' => $area->id,
                'nombre' => $area->nombre,
                'slug' => $area->slug,
                'icono' => $area->icono,
                'color' => $area->color,
                'orden' => $area->orden,
                'activa' => $area->activa,
                'categorias' => $area->categorias->map(fn (Categoria $c) => [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'descripcion' => $c->descripcion,
                    'activa' => $c->activa,
                    'cantidad_productos' => $c->productos()->count(),
                    'campos' => $c->campos->map(fn (CampoCategoria $campo) => [
                        'id' => $campo->id,
                        'nombre' => $campo->nombre,
                        'etiqueta' => $campo->etiqueta ?? $campo->nombre,
                        'tipo' => $campo->tipo,
                        'unidad' => $campo->unidad,
                        'obligatorio' => $campo->obligatorio,
                        'opciones' => $campo->opciones ?? [],
                    ])->values(),
                ])->values(),
            ])
            ->values();

        return Inertia::render('Admin/Catalogo/Index', [
            'areas' => $areas,
            'tab' => request()->query('tab', 'areas'),
        ]);
    }

    public function storeArea(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'icono' => ['nullable', 'string', 'max:60'],
            'color' => ['nullable', 'string', 'max:40'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ], [
            'nombre.required' => 'El nombre del área es obligatorio.',
        ]);

        Area::create($datos + [
            'slug' => Str::slug($datos['nombre']).'-'.Str::lower(Str::random(4)),
            'orden' => Area::max('orden') + 1,
            'activa' => true,
        ]);

        return back()->with('ok', 'Área creada.');
    }

    public function updateArea(Request $request, Area $area): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'icono' => ['nullable', 'string', 'max:60'],
            'color' => ['nullable', 'string', 'max:40'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activa' => ['boolean'],
        ]);

        $area->fill($datos);
        $area->activa = $request->boolean('activa');
        $area->save();

        return back()->with('ok', 'Área actualizada.');
    }

    public function destroyArea(Area $area): RedirectResponse
    {
        if ($area->categorias()->count() > 0) {
            return back()->with('error', 'Primero elimina las categorías de esta área.');
        }

        $area->delete();

        return back()->with('ok', 'Área eliminada.');
    }

    public function guardarCategoria(Request $request, ?Categoria $categoria = null): RedirectResponse
    {
        $datos = $request->validate([
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activa' => ['boolean'],
            'campos' => ['array'],
            'campos.*.nombre' => ['required', 'string', 'max:60'],
            'campos.*.etiqueta' => ['nullable', 'string', 'max:80'],
            'campos.*.tipo' => ['required', 'in:texto,numero,seleccion'],
            'campos.*.unidad' => ['nullable', 'string', 'max:30'],
            'campos.*.opciones' => ['nullable', 'array'],
            'campos.*.opciones.*' => ['string', 'max:80'],
            'campos.*.obligatorio' => ['boolean'],
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'area_id.required' => 'Elige el área.',
            'campos.*.nombre.required' => 'Cada campo necesita un nombre.',
        ]);

        $existente = $categoria ?? new Categoria();
        $existente->area_id = $datos['area_id'];
        $existente->nombre = $datos['nombre'];
        $existente->descripcion = $datos['descripcion'] ?? null;
        $existente->activa = $request->boolean('activa');
        $existente->slug = $existente->slug ?: Str::slug($datos['nombre']).'-'.Str::lower(Str::random(4));
        $existente->save();

        $existente->campos()->delete();

        foreach (array_values($datos['campos'] ?? []) as $i => $campo) {
            $opciones = null;
            if (($campo['tipo'] ?? '') === 'seleccion') {
                $opciones = array_values(array_filter($campo['opciones'] ?? []));
            }

            CampoCategoria::create([
                'categoria_id' => $existente->id,
                'nombre' => $campo['nombre'],
                'etiqueta' => $campo['etiqueta'] ?? null,
                'tipo' => $campo['tipo'],
                'unidad' => $campo['unidad'] ?? null,
                'opciones' => $opciones,
                'obligatorio' => filter_var($campo['obligatorio'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'orden' => $i + 1,
            ]);
        }

        return back()->with('ok', $categoria ? 'Categoría actualizada.' : 'Categoría creada.');
    }

    public function storeCategoria(Request $request): RedirectResponse
    {
        return $this->guardarCategoria($request);
    }

    public function updateCategoria(Request $request, Categoria $categoria): RedirectResponse
    {
        return $this->guardarCategoria($request, $categoria);
    }

    public function destroyCategoria(Categoria $categoria): RedirectResponse
    {
        if ($categoria->productos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: tiene productos asignados.');
        }

        $categoria->delete();

        return back()->with('ok', 'Categoría eliminada.');
    }

    public function uploadImagen(Request $request): JsonResponse
    {
        $request->validate([
            'imagen' => ['required', 'image', 'mimes:jpeg,png,webp,svg', 'max:2048'],
        ], [
            'imagen.required' => 'Selecciona una imagen.',
            'imagen.mimes' => 'Solo JPG, PNG, WebP o SVG.',
        ]);

        $archivo = $request->file('imagen');
        $ruta = $archivo->storePubliclyAs('categorias', Str::slug(pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME)).'-'.Str::random(6).'.'.$archivo->getClientOriginalExtension(), 'public');

        return response()->json([
            'ruta' => $ruta,
            'url' => ImageOptimizer::urlPublica(Storage::disk('public')->url($ruta)),
        ]);
    }
}