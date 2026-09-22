<script setup>
import { computed } from 'vue';
import Publico from '../../Layouts/Publico.vue';
import Moneda from '../../Components/Moneda.vue';

const props = defineProps({
    apartado: { type: Object, required: true },
    mensaje_whatsapp: { type: String, default: '' },
    whatsapp: { type: String, default: '' },
});

const enlaceWhatsapp = computed(() => {
    if (!props.mensaje_whatsapp) {
        return '#';
    }

    return `https://wa.me/${props.whatsapp}?text=${encodeURIComponent(props.mensaje_whatsapp)}`;
});
</script>

<template>
    <Publico>
        <div class="pantalla py-12">
            <div class="mx-auto max-w-2xl text-center">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-600">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-8 w-8"><path d="M4.5 12.5l5 5 10-11" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </span>
                <h1 class="titulo-marca mt-5 text-3xl font-bold text-slate-900">¡Apartado registrado!</h1>
                <p class="mt-2 text-slate-500">
                    Tu codigo es <span class="font-mono font-bold text-rojo-700">{{ apartado.codigo }}</span>.
                    Guardalo para hacer seguimiento del pedido.
                </p>
                <div class="mx-auto mt-4 max-w-lg rounded-xl bg-amber-50 p-4 text-sm text-amber-800">
                    <p class="font-semibold">Pago al contado y adelantado</p>
                    <p>Te contactaremos por WhatsApp para confirmar y coordinar el pago.</p>
                </div>
            </div>

            <div class="mx-auto mt-10 max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 bg-slate-50 px-5 py-3">
                    <p class="text-sm text-slate-600">
                        {{ apartado.nombre_cliente }} · <span class="font-medium">{{ apartado.telefono }}</span>
                    </p>
                    <span class="rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                        {{ apartado.estado }}
                    </span>
                </div>

                <ul class="divide-y divide-slate-100 px-5">
                    <li v-for="detalle in apartado.detalles" :key="detalle.nombre" class="flex items-center justify-between gap-3 py-3 text-sm">
                        <span class="min-w-0 truncate text-slate-700">
                            <span class="font-semibold">{{ detalle.cantidad }}×</span> {{ detalle.nombre }}
                        </span>
                        <Moneda :valor="detalle.subtotal" clase="shrink-0 font-semibold" />
                    </li>
                </ul>

                <div class="flex items-center justify-between border-t border-slate-200 px-5 py-4">
                    <span class="text-sm text-slate-500">Total</span>
                    <Moneda :valor="apartado.subtotal" clase="text-lg font-bold text-slate-900" />
                </div>
            </div>

            <div class="mx-auto mt-8 flex max-w-2xl flex-col gap-3 sm:flex-row">
                <a :href="enlaceWhatsapp" target="_blank" rel="noopener" class="boton-secundario flex-1 !border-green-500 !bg-green-600 !text-white hover:!bg-green-700">
                    Confirmar por WhatsApp
                </a>
                <a href="/catalogo" class="boton-primario flex-1">Seguir comprando</a>
            </div>
        </div>
    </Publico>
</template>