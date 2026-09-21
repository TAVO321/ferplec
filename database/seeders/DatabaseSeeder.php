<?php

namespace Database\Seeders;

use App\Models\Ajuste;
use App\Models\Area;
use App\Models\CampoCategoria;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->crearAjustes();
        $this->crearAdministrador();
        $this->crearCatalogo();
    }

    private function crearAjustes(): void
    {
        Ajuste::asignar('nombre_tienda', 'FERPLEC');
        Ajuste::asignar('whatsapp', env('WHATSAPP_NUMBER', '60000000'));
        Ajuste::asignar('direccion', env('STORE_ADDRESS', 'Av. Principal #000'));
    }

    private function crearAdministrador(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@ferplec.com'],
            [
                'nombre' => 'Administrador FERPLEC',
                'password' => 'admin123',
                'es_admin' => true,
                'activo' => true,
            ],
        );
    }

    private function crearCatalogo(): void
    {
        $ferreteria = $this->area('Ferretería', 'herramientas', 'Todo para el hogar, construcción y taller.', 'rojo');
        $electricidad = $this->area('Electricidad', 'rayo', 'Instalación y mantenimiento eléctrico domiciliario.', 'ambar');
        $plomeria = $this->area('Plomería', 'llave_agua', 'Tuberías, grifería y accesorios para agua.', 'azul');

        $this->categoria($ferreteria, 'Herramientas manuales', [
            ['nombre' => 'material', 'etiqueta' => 'Material', 'tipo' => 'texto'],
            ['nombre' => 'marca', 'etiqueta' => 'Marca', 'tipo' => 'texto'],
            ['nombre' => 'medida', 'etiqueta' => 'Tamaño', 'tipo' => 'texto'],
        ], 1);

        $tornillos = $this->categoria($ferreteria, 'Tornillos y fijaciones', [
            ['nombre' => 'material', 'etiqueta' => 'Material', 'tipo' => 'seleccion', 'opciones' => ['Acero', 'Galvanizado', 'Inoxidable']],
            ['nombre' => 'medida', 'etiqueta' => 'Medida', 'unidad' => 'mm', 'tipo' => 'numero'],
            ['nombre' => 'tipo', 'etiqueta' => 'Tipo', 'tipo' => 'seleccion', 'opciones' => ['Para madera', 'Para metal', 'Para plancha']],
        ], 2);

        $candados = $this->categoria($ferreteria, 'Candados y cadenas', [
            ['nombre' => 'material', 'etiqueta' => 'Material', 'tipo' => 'texto'],
            ['nombre' => 'medida', 'etiqueta' => 'Medida', 'unidad' => 'cm', 'tipo' => 'numero'],
            ['nombre' => 'tipo', 'etiqueta' => 'Tipo', 'tipo' => 'seleccion', 'opciones' => ['Larguero', 'Roseta', '+', 'Cadena']],
        ], 3);

        $cables = $this->categoria($electricidad, 'Cables y alambres', [
            ['nombre' => 'calibre', 'etiqueta' => 'Calibre', 'unidad' => 'AWG', 'tipo' => 'texto'],
            ['nombre' => 'material', 'etiqueta' => 'Conductor', 'tipo' => 'seleccion', 'opciones' => ['Cobre', 'Aluminio']],
            ['nombre' => 'voltaje', 'etiqueta' => 'Voltaje', 'unidad' => 'V', 'tipo' => 'numero'],
            ['nombre' => 'largo', 'etiqueta' => 'Largo', 'unidad' => 'm', 'tipo' => 'numero'],
        ], 1);

        $tomacorrientes = $this->categoria($electricidad, 'Interruptores y tomacorrientes', [
            ['nombre' => 'tipo', 'etiqueta' => 'Tipo', 'tipo' => 'seleccion', 'opciones' => ['Interruptor', 'Tomacorriente', 'Combinado']],
            ['nombre' => 'voltaje', 'etiqueta' => 'Voltaje', 'unidad' => 'V', 'tipo' => 'numero'],
            ['nombre' => 'amperaje', 'etiqueta' => 'Amperaje', 'unidad' => 'A', 'tipo' => 'numero'],
        ], 2);

        $focos = $this->categoria($electricidad, 'Lámparas y focos', [
            ['nombre' => 'tipo', 'etiqueta' => 'Tipo', 'tipo' => 'seleccion', 'opciones' => ['LED', 'Incandescente', 'Fluorescente']],
            ['nombre' => 'base', 'etiqueta' => 'Base', 'tipo' => 'seleccion', 'opciones' => ['E27', 'E14', 'GU10']],
            ['nombre' => 'potencia', 'etiqueta' => 'Potencia', 'unidad' => 'W', 'tipo' => 'numero'],
            ['nombre' => 'voltaje', 'etiqueta' => 'Voltaje', 'unidad' => 'V', 'tipo' => 'numero'],
        ], 3);

        $termicas = $this->categoria($electricidad, 'Llaves térmicas y tableros', [
            ['nombre' => 'amperaje', 'etiqueta' => 'Amperaje', 'unidad' => 'A', 'tipo' => 'numero'],
            ['nombre' => 'polos', 'etiqueta' => 'Polos', 'tipo' => 'seleccion', 'opciones' => ['1 polo', '2 polos', '3 polos']],
            ['nombre' => 'tipo', 'etiqueta' => 'Tipo', 'tipo' => 'seleccion', 'opciones' => ['Llave térmica', 'Diferencial', 'Tablero']],
        ], 4);

        $tuberias = $this->categoria($plomeria, 'Tuberías', [
            ['nombre' => 'material', 'etiqueta' => 'Material', 'tipo' => 'seleccion', 'opciones' => ['PVC', 'CPVC', 'PEX', 'Galvanizado']],
            ['nombre' => 'diametro', 'etiqueta' => 'Diámetro', 'unidad' => 'pulgadas', 'tipo' => 'texto'],
            ['nombre' => 'largo', 'etiqueta' => 'Largo', 'unidad' => 'm', 'tipo' => 'numero'],
            ['nombre' => 'presion', 'etiqueta' => 'Presión', 'unidad' => 'bar', 'tipo' => 'numero'],
        ], 1);

        $griferia = $this->categoria($plomeria, 'Grifería', [
            ['nombre' => 'tipo', 'etiqueta' => 'Tipo', 'tipo' => 'seleccion', 'opciones' => ['Caño', 'Llave de paso', 'Monocomando', 'Mezclador']],
            ['nombre' => 'material', 'etiqueta' => 'Material', 'tipo' => 'seleccion', 'opciones' => ['Cromado', 'Acero inoxidable', 'Bronce']],
            ['nombre' => 'medida', 'etiqueta' => 'Medida', 'unidad' => 'pulgadas', 'tipo' => 'texto'],
        ], 2);

        $conexiones = $this->categoria($plomeria, 'Conexiones y accesorios', [
            ['nombre' => 'tipo', 'etiqueta' => 'Tipo', 'tipo' => 'seleccion', 'opciones' => ['Codo', 'Unión', 'Válvula', 'Reducción']],
            ['nombre' => 'material', 'etiqueta' => 'Material', 'tipo' => 'seleccion', 'opciones' => ['PVC', 'Galvanizado', 'Latón']],
            ['nombre' => 'diametro', 'etiqueta' => 'Diámetro', 'unidad' => 'pulgadas', 'tipo' => 'texto'],
        ], 3);

        $selladores = $this->categoria($plomeria, 'Selladores y cintas', [
            ['nombre' => 'tipo', 'etiqueta' => 'Tipo', 'tipo' => 'seleccion', 'opciones' => ['Cinta teflón', 'Sellador silicona', 'Cemento PVC']],
            ['nombre' => 'presentacion', 'etiqueta' => 'Presentación', 'tipo' => 'texto'],
        ], 4);

        $this->producto($tornillos, 'Caja de tornillos para madera 2" (100 und.)', 12, 18,
            ['material' => 'Acero', 'medida' => '50', 'tipo' => 'Para madera'], true, 80);
        $this->producto($candados, 'Candado larguero 50 mm', 35, 0,
            ['material' => 'Acero', 'medida' => '50', 'tipo' => 'Larguero'], true, 25);
        $this->producto($cables, 'Cable eléctrico 12 AWG cobre (rollo 100 m)', 250, 0,
            ['calibre' => '12', 'material' => 'Cobre', 'voltaje' => '300', 'largo' => '100'], true, 15);
        $this->producto($tomacorrientes, 'Tomacorriente doble polarizado 15 A', 18, 0,
            ['tipo' => 'Tomacorriente', 'voltaje' => '250', 'amperaje' => '15'], true, 60);
        $this->producto($focos, 'Foco LED 9 W E27 luz blanca', 12, 0,
            ['tipo' => 'LED', 'base' => 'E27', 'potencia' => '9', 'voltaje' => '220'], true, 120);
        $this->producto($tuberias, 'Tubo PVC 1/2" presión 10 bar (3 m)', 28, 0,
            ['material' => 'PVC', 'diametro' => '1/2', 'largo' => '3', 'presion' => '10'], true, 40);
        $this->producto($griferia, 'Llave de paso 3/4 cromada', 35, 0,
            ['tipo' => 'Llave de paso', 'material' => 'Cromado', 'medida' => '3/4'], false, 30);
        $this->producto($conexiones, 'Codo PVC 1/2" cementado', 3, 0,
            ['tipo' => 'Codo', 'material' => 'PVC', 'diametro' => '1/2'], false, 200);
    }

    private function area(string $nombre, string $icono, string $descripcion, string $color): Area
    {
        return Area::updateOrCreate(
            ['slug' => $this->slug($nombre)],
            ['nombre' => $nombre, 'descripcion' => $descripcion, 'icono' => $icono, 'color' => $color],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $campos
     */
    private function categoria(Area $area, string $nombre, array $campos, int $orden): Categoria
    {
        $categoria = Categoria::updateOrCreate(
            ['slug' => $this->slug($nombre)],
            ['area_id' => $area->id, 'nombre' => $nombre, 'descripcion' => null, 'orden' => $orden, 'activa' => true],
        );

        $categoria->campos()->delete();

        foreach ($campos as $i => $campo) {
            CampoCategoria::create([
                'categoria_id' => $categoria->id,
                'nombre' => $campo['nombre'],
                'etiqueta' => $campo['etiqueta'],
                'tipo' => $campo['tipo'] ?? 'texto',
                'unidad' => $campo['unidad'] ?? null,
                'opciones' => $campo['opciones'] ?? null,
                'obligatorio' => false,
                'orden' => $i + 1,
            ]);
        }

        return $categoria;
    }

    /**
     * @param  array<string, string>  $atributos
     */
    private function producto(Categoria $categoria, string $nombre, float $precio, float $precioOferta, array $atributos, bool $destacado, int $stock): void
    {
        $producto = Producto::updateOrCreate(
            ['slug' => $this->slug($nombre)],
            [
                'categoria_id' => $categoria->id,
                'nombre' => $nombre,
                'descripcion' => 'Artículo de '.\Illuminate\Support\Str::lower($categoria->area->nombre).' para su proyecto.',
                'codigo' => strtoupper(substr($categoria->area->slug, 0, 3)).'-'.random_int(1000, 9999),
                'precio' => $precio,
                'precio_oferta' => $precioOferta > 0 ? $precioOferta : null,
                'stock' => $stock,
                'activo' => true,
                'destacado' => $destacado,
            ],
        );

        $producto->valores()->delete();

        foreach ($atributos as $nombreCampo => $valor) {
            $campo = $categoria->campos()->where('nombre', $nombreCampo)->first();
            if ($campo !== null) {
                \App\Models\ValorProducto::create([
                    'producto_id' => $producto->id,
                    'campo_id' => $campo->id,
                    'valor' => $valor,
                ]);
            }
        }
    }

    private function slug(string $texto): string
    {
        return \Illuminate\Support\Str::slug($texto);
    }
}