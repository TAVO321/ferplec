<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import InputError from '../../../Components/InputError.vue';
import Pagination from '../../../Components/Pagination.vue';
import Moneda from '../../../Components/Moneda.vue';

const props = defineProps({
    lotes: { type: Object, required: true },
    productos: { type: Array, required: true },
    proveedores: { type: Array, required: true },
});

const form = useForm({
    producto_id: '',
    proveedor_id: '',
    numero_lote: '',
    cantidad: 1,
    costo_unitario: '',
    fecha_ingreso: new Date().toISOString().split('T')[0],
    nota: '',
});

function guardar() {
    form.post(route('admin.lotes.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-4xl space-y-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Lotes de inventario</h2>
                <p class="text-sm text-slate-500">Entradas de stock por lote para control FIFO.</p>
            </div>

            <form class="rounded-2xl border border-slate-200 bg-white p-5" @submit.prevent="guardar">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-600">Nueva entrada</h3>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="etiqueta">Producto *</label>
                        <select v-model="form.producto_id" class="campo">
                            <option value="" disabled>Seleccionar</option>
                            <option v-for="p in productos" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                        </select>
                        <InputError :mensaje="form.errors.producto_id" />
                    </div>
                    <div>
                        <label class="etiqueta">Proveedor</label>
                        <select v-model="form.proveedor_id" class="campo">
                            <option value="">—</option>
                            <option v-for="p in proveedores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="etiqueta">Numero de lote</label>
                        <input v-model="form.numero_lote" type="text" class="campo">
                    </div>
                    <div>
                        <label class="etiqueta">Cantidad *</label>
                        <input v-model="form.cantidad" type="number" min="1" class="campo">
                        <InputError :mensaje="form.errors.cantidad" />
                    </div>
                    <div>
                        <label class="etiqueta">Costo unitario (Bs) *</label>
                        <input v-model="form.costo_unitario" type="number" min="0" step="0.01" class="campo">
                        <InputError :mensaje="form.errors.costo_unitario" />
                    </div>
                    <div>
                        <label class="etiqueta">Fecha de ingreso *</label>
                        <input v-model="form.fecha_ingreso" type="date" class="campo">
                        <InputError :mensaje="form.errors.fecha_ingreso" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="etiqueta">Nota</label>
                        <input v-model="form.nota" type="text" class="campo">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="boton-primario" :disabled="form.processing">Registrar entrada</button>
                </div>
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Producto</th>
                                <th class="px-4 py-3 font-semibold">Lote</th>
                                <th class="px-4 py-3 font-semibold">Disponible</th>
                                <th class="px-4 py-3 font-semibold">Costo</th>
                                <th class="px-4 py-3 font-semibold">Ingreso</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="l in lotes.data" :key="l.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ l.producto }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ l.proveedor }} · {{ l.numero_lote }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold" :class="l.cantidad_disponible > 0 ? 'bg-green-50 text-green-700' : 'bg-rose-50 text-rose-600'">
                                        {{ l.cantidad_disponible }} / {{ l.cantidad_inicial }}
                                    </span>
                                </td>
                                <td class="px-4 py-3"><Moneda :valor="l.costo_unitario" /></td>
                                <td class="px-4 py-3 text-slate-500">{{ l.fecha_ingreso }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-if="!lotes.data.length" class="px-5 py-8 text-center text-sm text-slate-400">No hay lotes registrados.</p>
                <Pagination
                    :paginas="lotes.paginas"
                    :pagina-actual="lotes.pagina_actual"
                    ruta="admin.lotes.index"
                />
            </div>
        </div>
    </AdminLayout>
</template>
