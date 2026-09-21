<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import Moneda from '../../../Components/Moneda.vue';

const props = defineProps({
    apartado: { type: Object, required: true },
    estados: { type: Array, required: true },
});

const estado = ref(props.apartado.estado);

function cambiarEstado() {
    router.patch(route('admin.apartados.estado', props.apartado.id), { estado: estado.value }, {
        preserveScroll: true,
    });
}

function eliminar() {
    if (!window.confirm(`¿Eliminar el apartado ${props.apartado.codigo}?\nLa acción no se puede deshacer.`)) {
        return;
    }
    router.delete(route('admin.apartados.destroy', props.apartado.id));
}
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-3xl space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <a href="/admin/apartados" class="text-sm font-semibold text-rojo-600 hover:text-rojo-700">← Volver a apartados</a>
                    <h2 class="mt-1 font-mono text-xl font-bold text-slate-900">{{ apartado.codigo }}</h2>
                    <p class="text-xs text-slate-400">Creado el {{ apartado.fecha }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Cliente</p>
                        <p class="mt-1 font-semibold text-slate-800">{{ apartado.nombre_cliente }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">WhatsApp</p>
                        <a :href="'https://wa.me/' + apartado.telefono" target="_blank" rel="noopener" class="mt-1 inline-flex font-semibold text-rojo-700 hover:text-rojo-600">
                            {{ apartado.telefono }}
                        </a>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Dirección</p>
                        <p class="mt-1 text-slate-700">{{ apartado.direccion || '—' }}</p>
                    </div>
                </div>

                <p v-if="apartado.nota" class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <strong>Nota del cliente:</strong> {{ apartado.nota }}
                </p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 bg-slate-50 px-5 py-3">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-600">Productos apartados</h3>
                </div>
                <ul class="divide-y divide-slate-100 px-5">
                    <li v-for="d in apartado.detalles" :key="d.nombre" class="flex items-center justify-between gap-3 py-3 text-sm">
                        <span class="text-slate-700">
                            <span class="font-semibold">{{ d.cantidad }}×</span> {{ d.nombre }}
                        </span>
                        <span class="flex items-center gap-4">
                            <Moneda :valor="d.subtotal" clase="shrink-0 font-semibold text-slate-800" />
                        </span>
                    </li>
                </ul>
                <div class="flex items-center justify-between border-t border-slate-200 px-5 py-4">
                    <span class="text-sm text-slate-500">Total</span>
                    <Moneda :valor="apartado.subtotal" clase="text-lg font-bold text-slate-900" />
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex items-center gap-3">
                    <label for="estado" class="text-sm font-semibold text-slate-600">Cambiar estado</label>
                    <select id="estado" v-model="estado" class="campo !w-auto">
                        <option v-for="e in estados" :key="e.clave" :value="e.clave">{{ e.etiqueta }}</option>
                    </select>
                    <button type="button" class="boton-primario" @click="cambiarEstado">Actualizar</button>
                </div>
                <button type="button" class="inline-flex items-center rounded-lg border border-rose-200 px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50" @click="eliminar">
                    Eliminar
                </button>
            </div>
        </div>
    </AdminLayout>
</template>