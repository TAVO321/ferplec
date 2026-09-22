<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import InputError from '../../../Components/InputError.vue';

const props = defineProps({
    proveedores: { type: Array, required: true },
});

const formNuevo = useForm({
    nombre: '',
    contacto: '',
    telefono: '',
    email: '',
    direccion: '',
    materiales: '',
});

function crear() {
    formNuevo.post(route('admin.proveedores.store'), {
        preserveScroll: true,
        onSuccess: () => formNuevo.reset(),
    });
}
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-4xl space-y-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Proveedores</h2>
                <p class="text-sm text-slate-500">Aliados para pedidos especiales y lotes de inventario.</p>
            </div>

            <form class="rounded-2xl border border-slate-200 bg-white p-5" @submit.prevent="crear">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-600">Nuevo proveedor</h3>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="etiqueta">Nombre *</label>
                        <input v-model="formNuevo.nombre" type="text" class="campo">
                        <InputError :mensaje="formNuevo.errors.nombre" />
                    </div>
                    <div>
                        <label class="etiqueta">Contacto</label>
                        <input v-model="formNuevo.contacto" type="text" class="campo">
                    </div>
                    <div>
                        <label class="etiqueta">Telefono</label>
                        <input v-model="formNuevo.telefono" type="text" class="campo">
                    </div>
                    <div>
                        <label class="etiqueta">Email</label>
                        <input v-model="formNuevo.email" type="email" class="campo">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="etiqueta">Direccion</label>
                        <input v-model="formNuevo.direccion" type="text" class="campo">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="etiqueta">Materiales que provee</label>
                        <input v-model="formNuevo.materiales" type="text" class="campo" placeholder="Cemento, fierro, ceramica...">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="boton-primario" :disabled="formNuevo.processing">Crear proveedor</button>
                </div>
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Proveedor</th>
                                <th class="px-4 py-3 font-semibold">Contacto</th>
                                <th class="px-4 py-3 font-semibold">Materiales</th>
                                <th class="px-4 py-3 font-semibold">Estado</th>
                                <th class="px-4 py-3 text-right font-semibold"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="p in proveedores" :key="p.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ p.nombre }}</td>
                                <td class="px-4 py-3 text-slate-600">
                                    <p>{{ p.telefono }}</p>
                                    <p class="text-xs">{{ p.email }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ p.materiales || '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="p.activo ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500'">
                                        {{ p.activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-xs text-slate-400">{{ p.lotes_count }} lotes</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-if="!proveedores.length" class="px-5 py-8 text-center text-sm text-slate-400">No hay proveedores registrados.</p>
            </div>
        </div>
    </AdminLayout>
</template>
