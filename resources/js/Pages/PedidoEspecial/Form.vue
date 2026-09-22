<script setup>
import { useForm } from '@inertiajs/vue3';
import Publico from '../../Layouts/Publico.vue';
import InputError from '../../Components/InputError.vue';

const props = defineProps({
    cliente: { type: Object, default: null },
});

const form = useForm({
    nombre_cliente: props.cliente?.nombre ?? '',
    telefono: props.cliente?.telefono ?? '',
    direccion: props.cliente?.direccion ?? '',
    ubicacion: props.cliente?.ubicacion_aproximada ?? '',
    producto_solicitado: '',
    cantidad: 1,
    unidad_de_medida: 'und',
    fecha_requerida: '',
    nota: '',
});

function enviar() {
    form.post(route('pedidos_especiales.store'));
}
</script>

<template>
    <Publico>
        <div class="pantalla py-10">
            <div class="mx-auto max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-wide text-rojo-600">Pedido especial</p>
                <h1 class="titulo-marca mt-1 text-3xl font-bold text-slate-900">Solicita material de construccion</h1>
                <p class="mt-2 text-slate-500">
                    Cemento, fierro, ceramica, ladrillo y otros materiales de gran volumen. Te cotizamos y coordinamos entrega.
                </p>

                <form class="mt-8 space-y-5 rounded-2xl border border-slate-200 bg-white p-6" @submit.prevent="enviar">
                    <div class="rounded-xl bg-amber-50 p-4 text-sm text-amber-800">
                        <p class="font-semibold">Cotizacion sujeta a confirmacion</p>
                        <p>Una vez cotizado, el pago se realiza al contado y adelantado.</p>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="etiqueta" for="nombre">Nombre completo *</label>
                            <input id="nombre" v-model="form.nombre_cliente" type="text" class="campo">
                            <InputError :mensaje="form.errors.nombre_cliente" />
                        </div>
                        <div>
                            <label class="etiqueta" for="telefono">WhatsApp *</label>
                            <input id="telefono" v-model="form.telefono" type="tel" class="campo">
                            <InputError :mensaje="form.errors.telefono" />
                        </div>
                        <div>
                            <label class="etiqueta" for="direccion">Direccion</label>
                            <input id="direccion" v-model="form.direccion" type="text" class="campo">
                            <InputError :mensaje="form.errors.direccion" />
                        </div>
                        <div>
                            <label class="etiqueta" for="ubicacion">Ubicacion aproximada</label>
                            <input id="ubicacion" v-model="form.ubicacion" type="text" class="campo">
                            <InputError :mensaje="form.errors.ubicacion" />
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <label class="etiqueta" for="producto">Producto solicitado *</label>
                            <input id="producto" v-model="form.producto_solicitado" type="text" class="campo" placeholder="Ej. Cemento Pacasmayo 42.5 kg">
                            <InputError :mensaje="form.errors.producto_solicitado" />
                        </div>
                        <div>
                            <label class="etiqueta" for="cantidad">Cantidad *</label>
                            <input id="cantidad" v-model="form.cantidad" type="number" min="1" class="campo">
                            <InputError :mensaje="form.errors.cantidad" />
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="etiqueta" for="unidad">Unidad de medida *</label>
                            <input id="unidad" v-model="form.unidad_de_medida" type="text" class="campo" placeholder="und, bolsa, m2, tonelada...">
                            <InputError :mensaje="form.errors.unidad_de_medida" />
                        </div>
                        <div>
                            <label class="etiqueta" for="fecha">Fecha requerida</label>
                            <input id="fecha" v-model="form.fecha_requerida" type="date" class="campo">
                            <InputError :mensaje="form.errors.fecha_requerida" />
                        </div>
                    </div>

                    <div>
                        <label class="etiqueta" for="nota">Nota</label>
                        <textarea id="nota" v-model="form.nota" rows="3" class="campo" placeholder="Marca preferida, indicaciones de entrega, etc." />
                        <InputError :mensaje="form.errors.nota" />
                    </div>

                    <button type="submit" class="boton-primario w-full !py-3" :disabled="form.processing">
                        {{ form.processing ? 'Enviando...' : 'Solicitar cotizacion' }}
                    </button>
                </form>
            </div>
        </div>
    </Publico>
</template>
