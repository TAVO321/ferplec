<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import Moneda from '../../../Components/Moneda.vue';
import Pagination from '../../../Components/Pagination.vue';

const props = defineProps({
    apartados: { type: Object, required: true },
    total: { type: Number, required: true },
    estados: { type: Array, required: true },
    filtros: { type: Object, required: true },
});

const estado = ref(props.filtros.estado);
const q = ref(props.filtros.q);

const filtrosActivos = () => ({
    estado: estado.value || undefined,
    q: q.value || undefined,
});

function buscar() {
    router.get(route('admin.apartados.index'), filtrosActivos(), {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Apartados</h2>
                <p class="text-sm text-slate-500">{{ total }} apartados en total</p>
            </div>

            <form class="rounded-2xl border border-slate-200 bg-white p-4" @submit.prevent="buscar">
                <div class="grid gap-3 sm:grid-cols-[1fr_220px_auto]">
                    <input v-model="q" class="campo" type="search" placeholder="Buscar por código, cliente o teléfono…">
                    <select v-model="estado" class="campo">
                        <option value="">Todos los estados</option>
                        <option v-for="e in estados" :key="e.clave" :value="e.clave">{{ e.etiqueta }}</option>
                    </select>
                    <button type="submit" class="boton-secundario">Filtrar</button>
                </div>
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Código</th>
                                <th class="px-4 py-3 font-semibold">Cliente</th>
                                <th class="px-4 py-3 font-semibold">Teléfono</th>
                                <th class="px-4 py-3 font-semibold">Ítems</th>
                                <th class="px-4 py-3 font-semibold">Total</th>
                                <th class="px-4 py-3 font-semibold">Estado</th>
                                <th class="px-4 py-3 font-semibold">Fecha</th>
                                <th class="px-4 py-3 text-right font-semibold"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="a in apartados.data" :key="a.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-mono font-semibold text-rojo-700">{{ a.codigo }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ a.nombre_cliente }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ a.telefono }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ a.cantidad }}</td>
                                <td class="px-4 py-3"><Moneda :valor="a.subtotal" clase="font-semibold text-slate-800" /></td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="a.clase_estado">
                                        {{ a.etiqueta_estado }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-400">{{ a.fecha }}</td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="route('admin.apartados.show', a.id)" class="inline-flex h-8 items-center rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-600 hover:border-rojo-500 hover:text-rojo-700">
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p v-if="!apartados.data.length" class="px-5 py-10 text-center text-sm text-slate-400">
                    No hay apartados que coincidan con el filtro.
                </p>
            </div>

            <Pagination
                :paginas="apartados.paginas"
                :pagina-actual="apartados.pagina_actual"
                ruta="admin.apartados.index"
                :preserve-query="filtrosActivos()"
            />
        </div>
    </AdminLayout>
</template>