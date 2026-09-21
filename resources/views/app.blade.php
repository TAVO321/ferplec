<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#D9232D">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title inertia>{{ config('app.name', 'FERPLEC') }}</title>
        @vite('resources/js/app.js')
        @inertiaHead
    </head>
    <body class="min-h-screen bg-marfil text-slate-900 antialiased">
        @inertia
    </body>
</html>