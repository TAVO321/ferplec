<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AjusteAdminController;
use App\Http\Controllers\Admin\CatalogAdminController;
use App\Http\Controllers\Admin\LoteAdminController;
use App\Http\Controllers\Admin\ProductoAdminController;
use App\Http\Controllers\Admin\ProveedorAdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:global')->group(function () {
    Route::get('/', [StorefrontController::class, 'home'])->name('home');
    Route::get('/catalogo', [StorefrontController::class, 'index'])->name('catalogo');
    Route::get('/catalogo/{producto:slug}', [StorefrontController::class, 'show'])->name('catalogo.show');
});

Route::middleware('throttle:global')->prefix('carrito')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
});

Route::get('/acceso', function () {
    return redirect()->route('admin.dashboard');
})->name('admin.login');

Route::post('/acceso', [AdminAuthController::class, 'login'])->name('admin.login.store');

Route::post('/acceso/salir', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware(['acceso-libre', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/productos', [ProductoAdminController::class, 'index'])->name('productos.index');
    Route::get('/productos/nuevo', [ProductoAdminController::class, 'create'])->name('productos.create');
    Route::post('/productos', [ProductoAdminController::class, 'store'])->name('productos.store');
    Route::get('/productos/{producto}/editar', [ProductoAdminController::class, 'edit'])->name('productos.edit');
    Route::patch('/productos/{producto}', [ProductoAdminController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{producto}', [ProductoAdminController::class, 'destroy'])->name('productos.destroy');
    Route::post('/productos/imagen', [ProductoAdminController::class, 'uploadImagen'])->name('productos.imagen');

    Route::get('/catalogo', [CatalogAdminController::class, 'index'])->name('catalogo');
    Route::post('/areas', [CatalogAdminController::class, 'storeArea'])->name('catalog.areas.store');
    Route::patch('/areas/{area}', [CatalogAdminController::class, 'updateArea'])->name('catalog.areas.update');
    Route::delete('/areas/{area}', [CatalogAdminController::class, 'destroyArea'])->name('catalog.areas.destroy');
    Route::post('/categorias', [CatalogAdminController::class, 'storeCategoria'])->name('catalog.categorias.store');
    Route::patch('/categorias/{categoria}', [CatalogAdminController::class, 'updateCategoria'])->name('catalog.categorias.update');
    Route::delete('/categorias/{categoria}', [CatalogAdminController::class, 'destroyCategoria'])->name('catalog.categorias.destroy');
    Route::post('/categorias/imagen', [CatalogAdminController::class, 'uploadImagen'])->name('catalog.imagen');

    Route::get('/proveedores', [ProveedorAdminController::class, 'index'])->name('proveedores.index');
    Route::post('/proveedores', [ProveedorAdminController::class, 'store'])->name('proveedores.store');
    Route::patch('/proveedores/{proveedor}', [ProveedorAdminController::class, 'update'])->name('proveedores.update');
    Route::delete('/proveedores/{proveedor}', [ProveedorAdminController::class, 'destroy'])->name('proveedores.destroy');

    Route::get('/lotes', [LoteAdminController::class, 'index'])->name('lotes.index');
    Route::post('/lotes', [LoteAdminController::class, 'store'])->name('lotes.store');

    Route::get('/ajustes', [AjusteAdminController::class, 'index'])->name('ajustes');
    Route::patch('/ajustes', [AjusteAdminController::class, 'update'])->name('ajustes.update');
});
