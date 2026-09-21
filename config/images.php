<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Base pública para imágenes
    |--------------------------------------------------------------------------
    |
    | Permite reescribir el origen de las URLs de imágenes hacia un bucket/CDN
    | sin tocar la base de datos. Útil al migrar de un dominio o bucket a otro.
    |
    */
    'public_url' => env('IMAGES_PUBLIC_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Asumir que las variantes WebP existen
    |--------------------------------------------------------------------------
    |
    | Cuando está activo, no se hacen peticiones HEAD para verificar si una
    | variante WebP remota existe. Reduce el tiempo de carga en producción.
    | Si una variante no existe, el navegador recibe 404 y usa la original.
    |
    */
    'assume_variants_exist' => env('IMAGES_ASSUME_VARIANTS_EXIST', false),
];