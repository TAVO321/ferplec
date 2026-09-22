<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import Moneda from '../../../Components/Moneda.vue';
import Pagination from '../../../Components/Pagination.vue';

const props = defineProps({
    productos: { type: Object, required: true },
    total: { type: Number, required: true },
    categorias: { type: Array, required: true },
    filtros: { type: Object, required: true },
});

const q = ref(props.filtros.q);
const categoria = ref(props.filtros.categoria);
const agotados = ref(props.filtros.agotados);

const filtrosActivos = () => ({
    q: q.value || undefined,
    categoria: categoria.value || undefined,
    agotados: agotados.value || undefined,
});

function buscar() {
    router.get(route('admin.productos.index'), filtrosActivos(), {
        preserveState: true,
        replace: true,
    });
}

function eliminar(producto) {
    if (!window.confirm(`¿Eliminar "${producto.nombre}"?\nSe borrarán también sus imágenes.`)) {
        return;
    }
    router.delete(route('admin.productos.destroy', producto.id), {
        preserveScroll: true,
        onError: (err) => window.alert(Object.values(err).join('\n')),
    });
}
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Productos</h2>
                    <p class="text-sm text-slate-500">{{ total }} productos en total</p>
                </div>
                <Link :href="route('admin.productos.create')" class="boton-primario">+ Nuevo producto</Link>
            </div>

            <form class="rounded-2xl border border-slate-200 bg-white p-4" @submit.prevent="buscar">
                <div class="grid gap-3 sm:grid-cols-[1fr_220px_auto]">
                    <input v-model="q" class="campo" type="search" placeholder="Buscar por nombre o código…">
                    <select v-model="categoria" class="campo">
                        <option value="">Todas las categorías</option>
                        <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                    </select>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input v-model="agotados" type="checkbox" class="rounded border-slate-300 text-rojo-600 focus:ring-rojo-500">
                            Solo agotados
                        </label>
                        <button type="submit" class="boton-secundario">Filtrar</button>
                    </div>
                </div>
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Producto</th>
                                <th class="px-4 py-3 font-semibold">Categoria</th>
                                <th class="px-4 py-3 font-semibold">Marca</th>
                                <th class="px-4 py-3 font-semibold">Precio</th>
                                <th class="px-4 py-3 font-semibold">Stock</th>
                                <th class="px-4 py-3 font-semibold">Estado</th>
                                <th class="px-4 py-3 text-right font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="p in productos.data" :key="p.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100">
                                            <img v-if="p.imagen" :src="p.imagen" :alt="p.nombre" class="h-full w-full object-cover">
                                            <span v-else class="titulo-marca font-bold text-rojo-600">F</span>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-800">{{ p.nombre }}</p>
                                            <p class="font-mono text-xs text-slate-400">{{ p.codigo || '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-slate-700">{{ p.categoria }}</p>
                                    <p class="text-xs text-slate-400">{{ p.area }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-slate-700">{{ p.marca || '—' }}</p>
                                    <p class="text-xs text-slate-400">{{ p.unidad_de_medida }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <Moneda :valor="p.precio" clase="font-semibold text-slate-800" />
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold" :class="p.stock === 0 ? 'bg-rose-50 text-rose-600' : p.stock <= 5 ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700'">
                                        {{ p.stock }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="p.activo ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500'">
                                            {{ p.activo ? 'Activo' : 'Oculto' }}
                                        </span>
                                        <span v-if="p.destacado" class="rounded-full bg-rojo-50 px-2.5 py-1 text-xs font-semibold text-rojo-700">Destacado</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('admin.productos.edit', p.id)" class="inline-flex h-8 items-center rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-600 hover:border-rojo-500 hover:text-rojo-700">
                                            Editar
                                        </Link>
                                        <button type="button" class="inline-flex h-8 items-center rounded-lg border border-rose-200 px-3 text-xs font-semibold text-rose-600 hover:bg-rose-50" @click="eliminar(p)">
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination
                :paginas="productos.paginas"
                :pagina-actual="productos.pagina_actual"
                ruta="admin.productos.index"
                :preserve-query="filtrosActivos()"
            />
        </div>
    </AdminLayout>
</template>