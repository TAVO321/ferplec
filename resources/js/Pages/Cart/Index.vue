<script setup>
import { router } from '@inertiajs/vue3';
import Publico from '../../Layouts/Publico.vue';
import Moneda from '../../Components/Moneda.vue';

const props = defineProps({
    items: { type: Array, required: true },
    total: { type: String, default: '0' },
});

function cambiarCantidad(productoId, cantidad) {
    router.patch(route('cart.update', { productoId }), { cantidad }, {
        preserveScroll: true,
        onError: () => {},
    });
}

function quitar(productoId) {
    router.delete(route('cart.destroy', { productoId }), { preserveScroll: true });
}
</script>

<template>
    <Publico>
        <div class="pantalla py-10">
            <h1 class="titulo-marca text-3xl font-bold text-slate-900">Tu carrito</h1>

            <div v-if="items.length" class="mt-8 grid gap-8 lg:grid-cols-[1fr_320px]">
                <div class="space-y-3">
                    <div v-for="item in items" :key="item.producto_id" class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center">
                        <div class="flex flex-1 items-center gap-4">
                            <a :href="route('catalogo.show', item.slug)" class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100">
                                <img v-if="item.imagen" :src="item.imagen" :alt="item.nombre" class="h-full w-full object-cover">
                                <span v-else class="titulo-marca text-2xl font-bold text-rojo-600">F</span>
                            </a>
                            <div class="min-w-0">
                                <a :href="route('catalogo.show', item.slug)" class="block truncate text-sm font-semibold text-slate-800 hover:text-rojo-700">
                                    {{ item.nombre }}
                                </a>
                                <Moneda v-if="item.precio_anterior" :valor="item.precio_anterior" clase="text-xs text-slate-400 line-through" />
                                <Moneda :valor="item.precio" clase="text-base font-bold text-slate-900" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 sm:justify-end">
                            <div class="flex items-center rounded-xl border border-slate-200">
                                <button type="button" class="flex h-10 w-10 items-center justify-center text-lg text-slate-500 hover:text-rojo-600" @click="cambiarCantidad(item.producto_id, item.cantidad - 1)">−</button>
                                <span class="w-9 text-center text-sm font-semibold">{{ item.cantidad }}</span>
                                <button type="button" class="flex h-10 w-10 items-center justify-center text-lg text-slate-500 hover:text-rojo-600 disabled:opacity-40" :disabled="item.cantidad >= item.stock" @click="cambiarCantidad(item.producto_id, item.cantidad + 1)">+</button>
                            </div>
                            <Moneda :valor="item.subtotal" clase="w-24 text-right text-sm font-bold text-slate-900" />
                            <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" :aria-label="`Quitar ${item.nombre}`" @click="quitar(item.producto_id)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path d="M19 7l-.9 12.1A2 2 0 0116.1 21H7.9a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="h-fit rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Resumen</h2>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-slate-500">
                            <dt>Productos</dt>
                            <dd>{{ items.length }}</dd>
                        </div>
                        <div class="flex justify-between border-t border-slate-100 pt-3 text-base font-bold text-slate-900">
                            <dt>Total</dt>
                            <dd><Moneda :valor="total" /></dd>
                        </div>
                    </dl>
                    <a href="/apartado" class="boton-primario mt-5 w-full">Continuar</a>
                    <a href="/catalogo" class="boton-secundario mt-2 w-full">Seguir comprando</a>
                </div>
            </div>

            <div v-else class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center">
                <p class="text-5xl">🛒</p>
                <h2 class="mt-4 text-lg font-semibold text-slate-700">Tu carrito está vacío</h2>
                <p class="mt-1 text-sm text-slate-500">Agregá productos del catálogo para poder apartarlos.</p>
                <a href="/catalogo" class="boton-primario mt-6">Ver catálogo</a>
            </div>
        </div>
    </Publico>
</template>