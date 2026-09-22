<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ajustes de la tienda (nombre, WhatsApp, dirección) con caché simple.
 */
class Ajuste extends Model
{
    protected $table = 'ajustes';

    protected $fillable = ['clave', 'valor'];

    public $timestamps = false;

    public static function obtener(string $clave, mixed $porDefecto = null): mixed
    {
        $valor = static::query()->where('clave', $clave)->value('valor');

        return $valor === null ? $porDefecto : $valor;
    }

    public static function asignar(string $clave, mixed $valor): void
    {
        static::updateOrCreate(['clave' => $clave], ['valor' => (string) $valor]);
    }

    public static function nombreTienda(): string
    {
        return (string) static::obtener('nombre_tienda', config('app.name'));
    }

    public static function whatsapp(): string
    {
        return (string) static::obtener('whatsapp', env('WHATSAPP_NUMBER', ''));
    }

    public static function direccion(): string
    {
        return (string) static::obtener('direccion', env('STORE_ADDRESS', ''));
    }

    public static function moneda(): string
    {
        return (string) static::obtener('moneda', env('CURRENCY', 'Bs'));
    }
}
