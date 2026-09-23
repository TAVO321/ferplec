<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import InputError from '../../../Components/InputError.vue';

const props = defineProps({
    ajustes: { type: Object, required: true },
});

const form = useForm({
    nombre_tienda: props.ajustes.nombre_tienda ?? 'FERPLEC',
    whatsapp: props.ajustes.whatsapp ?? '',
    direccion: props.ajustes.direccion ?? '',
    moneda: props.ajustes.moneda ?? 'Bs',
});

function guardar() {
    form.patch(route('admin.ajustes.update'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AdminLayout>
        <form class="mx-auto max-w-2xl space-y-6" @submit.prevent="guardar">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Ajustes</h2>
                    <p class="text-sm text-slate-500">Datos generales de la tienda.</p>
                </div>
                <button type="submit" class="boton-primario" :disabled="form.processing">
                    {{ form.processing ? 'Guardando…' : 'Guardar ajustes' }}
                </button>
            </div>

            <div class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6">
                <div>
                    <label class="etiqueta" for="nombre_tienda">Nombre de la tienda *</label>
                    <input id="nombre_tienda" v-model="form.nombre_tienda" type="text" class="campo">
                    <InputError :mensaje="form.errors.nombre_tienda" />
                </div>

                <div>
                    <label class="etiqueta" for="whatsapp">WhatsApp</label>
                    <input id="whatsapp" v-model="form.whatsapp" type="text" class="campo" placeholder="Ej. 59171234567">
                    <p class="mt-1 text-xs text-slate-400">Con prefijo de país, solo números. Se usa para los pedidos por WhatsApp.</p>
                    <InputError :mensaje="form.errors.whatsapp" />
                </div>

                <div>
                    <label class="etiqueta" for="direccion">Direccion</label>
                    <input id="direccion" v-model="form.direccion" type="text" class="campo" placeholder="Zona, calle y numero">
                    <InputError :mensaje="form.errors.direccion" />
                </div>

                <div>
                    <label class="etiqueta" for="moneda">Moneda</label>
                    <input id="moneda" v-model="form.moneda" type="text" class="campo" placeholder="Ej. Bs">
                    <p class="mt-1 text-xs text-slate-400">Simbolo que se muestra en precios.</p>
                    <InputError :mensaje="form.errors.moneda" />
                </div>

                <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                    <p class="font-semibold text-slate-800">Moneda</p>
                    <p class="mt-1">Simbolo actual: <strong>{{ ajustes.moneda }}</strong>. Se usa para todos los precios.</p>
                </div>
            </div>

            <div class="flex justify-end pb-8">
                <button type="submit" class="boton-primario" :disabled="form.processing">
                    {{ form.processing ? 'Guardando…' : 'Guardar ajustes' }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>