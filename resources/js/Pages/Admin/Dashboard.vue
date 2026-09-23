<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';

defineProps({
    estadisticas: { type: Object, required: true },
    productos_descargados: { type: Array, default: () => [] },
});

const tarjetas = [
    { etiqueta: 'Productos', clave: 'productos', ruta: 'admin.productos.index' },
    { etiqueta: 'Activos', clave: 'productos_activos', ruta: 'admin.productos.index' },
    { etiqueta: 'Agotados', clave: 'productos_sin_stock', ruta: 'admin.productos.index' },
    { etiqueta: 'Categorías', clave: 'categorias', ruta: 'admin.catalogo' },
];
</script>

<template>
    <AdminLayout>
        <div class="space-y-8">
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <Link
                    v-for="tarjeta in tarjetas"
                    :key="tarjeta.etiqueta"
                    :href="route(tarjeta.ruta)"
                    class="tarjeta p-5 transition hover:border-rojo-500 hover:shadow-md"
                >
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ tarjeta.etiqueta }}</p>
                    <p class="titulo-marca mt-2 text-3xl font-bold text-slate-900">{{ estadisticas[tarjeta.clave] }}</p>
                </Link>
            </div>

            <div class="grid gap-6 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Productos activos</p>
                    <p class="titulo-marca mt-2 text-2xl font-bold text-slate-900">{{ estadisticas.productos_activos }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Stock bajo (≤ 5)</p>
                    <p class="titulo-marca mt-2 text-2xl font-bold text-amber-600">{{ estadisticas.stock_bajo }}</p>
                </div>
            </div>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-5 py-3">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-600">Productos con stock bajo</h2>
                </div>

                <ul v-if="productos_descargados.length" class="divide-y divide-slate-100">
                    <li v-for="producto in productos_descargados" :key="producto.id" class="flex items-center justify-between px-5 py-3">
                        <Link :href="route('admin.productos.edit', producto.id)" class="truncate text-sm font-medium text-slate-700 hover:text-rojo-700">
                            {{ producto.nombre }}
                        </Link>
                        <span
                            class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold"
                            :class="producto.stock === 0 ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-700'"
                        >
                            {{ producto.stock === 0 ? 'Agotado' : `${producto.stock} und.` }}
                        </span>
                    </li>
                </ul>

                <p v-else class="px-5 py-8 text-center text-sm text-slate-400">Todo el stock está al día.</p>
            </section>
        </div>
    </AdminLayout>
</template>