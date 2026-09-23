<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Optimiza imágenes (WebP) y reescribe el origen de las URLs hacia la base
 * configurada (`IMAGES_PUBLIC_URL`), igual que un CDN o bucket R2, sin tocar
 * la base de datos.
 */
class ImageOptimizer
{
    /** Anchos (px) de las variantes que se generan por imagen. */
    public const ANCHOS = [480, 1200];

    /** Ancho de la variante usada en tarjetas. */
    public const ANCHO_TARJETA = 480;

    /** Las URLs se generan únicas por subida, seguras para cache inmutable. */
    public const CACHE_CONTROL = 'public, max-age=31536000, immutable';

    private const PREFIJO_CACHE = 'fimg.opt.v1.';

    /**
     * Base pública configurada, o null si no se definió.
     */
    public static function baseConfigurada(): ?string
    {
        $configurada = (string) config('images.public_url', '');

        return $configurada === '' ? null : rtrim($configurada, '/');
    }

    /**
     * Bases públicas candidatas en orden de prioridad.
     *
     * @return array<int, string>
     */
    private static function basesPublicas(): array
    {
        $bases = [];

        if (($configurada = static::baseConfigurada()) !== null) {
            $bases[] = $configurada;
        }

        $storage = rtrim((string) Storage::disk('public')->url(''), '/');
        if ($storage !== '') {
            $bases[] = $storage;
        }

        return array_values(array_unique($bases));
    }

    /**
     * Convierte una URL pública a la ruta del disco público, o null.
     */
    public static function rutaDisco(string $url): ?string
    {
        foreach (static::basesPublicas() as $base) {
            if (str_starts_with($url, $base.'/')) {
                return substr($url, strlen($base) + 1);
            }
        }

        return null;
    }

    /**
     * Alias de urlPublica().
     */
    public static function publicUrl(?string $url): ?string
    {
        return static::urlPublica($url);
    }

    /**
     * Reescribe el origen de una URL absoluta hacia la base configurada
     * (`IMAGES_PUBLIC_URL`) cuando difiere. Devuelve la URL tal cual si no aplica.
     */
    public static function urlPublica(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return $url;
        }

        $configurada = static::baseConfigurada();

        if (static::origen($url) === null) {
            $base = $configurada ?? rtrim((string) Storage::disk('public')->url(''), '/');

            return $base.'/'.ltrim($url, '/');
        }

        if ($configurada === null) {
            return $url;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);

        if ($path === ''
            || $path === '/'
            || str_starts_with($path, '/storage')
            || static::origen($url) === static::origen($configurada)) {
            return $url;
        }

        return $configurada.$path;
    }

    /**
     * Ruta en disco de la variante de un ancho para una ruta dada.
     */
    public static function rutaVariante(string $ruta, int $ancho): string
    {
        $dir = Str::beforeLast($ruta, '/');
        $nombre = Str::afterLast($ruta, '/');
        $raiz = pathinfo($nombre, PATHINFO_FILENAME);

        return ($dir === '' ? '' : $dir.'/').$raiz.'-'.(int) $ancho.'w.webp';
    }

    /**
     * Devuelve la URL pública de la variante si existe; si no, la original.
     */
    public static function urlVariante(string $url, int $ancho): string
    {
        $ruta = static::rutaDisco($url);

        if ($ruta === null || ! static::existeVariante($url, $ancho)) {
            return $url;
        }

        $base = static::baseUrl();

        return $base.'/'.static::rutaVariante($ruta, $ancho);
    }

    public static function baseUrl(): string
    {
        return static::baseConfigurada() ?? rtrim((string) Storage::disk('public')->url(''), '/');
    }

    public static function origen(string $url): ?string
    {
        $esquema = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);

        if (! $esquema || ! $host) {
            return null;
        }

        return $esquema.'://'.$host;
    }

    /**
     * Indica si la variante de un ancho existe, con caché permanente.
     */
    public static function existeVariante(string $url, int $ancho): bool
    {
        $ruta = static::rutaDisco($url);

        if ($ruta === null) {
            return false;
        }

        $clave = static::claveCache($ruta, $ancho);

        if (Cache::has($clave)) {
            return (bool) Cache::get($clave);
        }

        $existe = false;

        try {
            $existe = Storage::disk('public')->exists(static::rutaVariante($ruta, $ancho));
        } catch (\Throwable) {
            $existe = false;
        }

        if (! $existe && static::debeAsumirVariantes()) {
            try {
                $existe = Storage::disk('public')->exists($ruta);
            } catch (\Throwable) {
                $existe = false;
            }
        }

        Cache::forever($clave, $existe);

        return $existe;
    }

    public static function debeAsumirVariantes(): bool
    {
        return filter_var(
            config('images.assume_variants_exist', env('IMAGES_ASSUME_VARIANTS_EXIST', false)),
            FILTER_VALIDATE_BOOLEAN,
        );
    }

    public static function olvidarCache(string $ruta): void
    {
        foreach (static::ANCHOS as $ancho) {
            Cache::forget(static::claveCache($ruta, $ancho));
        }
    }

    private static function claveCache(string $ruta, int $ancho): string
    {
        return static::PREFIJO_CACHE.Str::lower($ruta).'.'.$ancho;
    }

    /**
     * Genera las variantes WebP de una imagen recién guardada, con la ruta
     * local del archivo para no re-descargarlo. Si falta ImageMagick o el
     * formato no lo soporta, no rompe nada y deja el original.
     */
    public static function optimizar(string $ruta, ?string $fuente = null): void
    {
        static::olvidarCache($ruta);

        $disco = Storage::disk('public');

        if ($fuente === null || ! is_file($fuente)) {
            return;
        }

        if (in_array(strtolower(pathinfo($ruta, PATHINFO_EXTENSION)), ['webp', 'gif', 'svg'], true)) {
            return;
        }

        $anchoOriginal = static::anchoImagen($fuente);

        foreach (static::ANCHOS as $ancho) {
            if ($anchoOriginal !== null && $anchoOriginal <= $ancho) {
                Cache::forever(static::claveCache($ruta, $ancho), false);

                continue;
            }

            $salida = tempnam(sys_get_temp_dir(), 'ferplec-webp').'.webp';
            $comando = vsprintf(
                'magick %s -auto-orient -resize %s -strip -quality 82 -define webp:method=6 %s',
                [escapeshellarg($fuente), (int) $ancho, escapeshellarg($salida)],
            );

            $salidaOk = false;
            @exec($comando.' 2>/dev/null', $ignorado, $exit);
            $salidaOk = ($exit ?? 1) === 0 && is_file($salida) && filesize($salida) > 0;

            if ($salidaOk) {
                $disco->put(
                    static::rutaVariante($ruta, $ancho),
                    file_get_contents($salida),
                    ['CacheControl' => static::CACHE_CONTROL],
                );
            }

            @unlink($salida);

            Cache::forever(static::claveCache($ruta, $ancho), $disco->exists(static::rutaVariante($ruta, $ancho)));
        }
    }

    private static function anchoImagen(string $fuente): ?int
    {
        $comando = 'magick '.escapeshellarg($fuente).' -format "%w" info: 2>/dev/null';
        @exec($comando, $salida, $exit);

        $ancho = isset($salida[0]) ? filter_var($salida[0], FILTER_VALIDATE_INT) : false;

        return ($exit ?? 1) === 0 && $ancho !== false ? $ancho : null;
    }
}
