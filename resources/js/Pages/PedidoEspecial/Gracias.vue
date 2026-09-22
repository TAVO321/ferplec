<script setup>
import { computed } from 'vue';
import Publico from '../../Layouts/Publico.vue';

const props = defineProps({
    pedido: { type: Object, required: true },
    mensaje_whatsapp: { type: String, default: '' },
    whatsapp: { type: String, default: '' },
});

const enlaceWhatsapp = computed(() => {
    if (!props.whatsapp) {
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
                <h1 class="titulo-marca mt-5 text-3xl font-bold text-slate-900">Solicitud registrada</h1>
                <p class="mt-2 text-slate-500">
                    Tu codigo es <span class="font-mono font-bold text-rojo-700">{{ pedido.codigo }}</span>.
                    Te contactaremos por WhatsApp con la cotizacion.
                </p>
                <div class="mx-auto mt-4 max-w-lg rounded-xl bg-amber-50 p-4 text-sm text-amber-800">
                    <p class="font-semibold">Pago al contado y adelantado</p>
                    <p>Una vez aceptes la cotizacion, coordinamos el pago y la entrega.</p>
                </div>

                <div class="mx-auto mt-8 max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white text-left">
                    <div class="border-b border-slate-200 bg-slate-50 px-5 py-3">
                        <p class="font-mono font-semibold text-rojo-700">{{ pedido.codigo }}</p>
                    </div>
                    <div class="p-5">
                        <p class="text-sm text-slate-500">Producto solicitado</p>
                        <p class="text-lg font-semibold text-slate-900">{{ pedido.producto_solicitado }}</p>
                        <p class="mt-3 text-sm text-slate-500">Cantidad</p>
                        <p class="text-lg font-semibold text-slate-900">{{ pedido.cantidad }} {{ pedido.unidad_de_medida }}</p>
                        <p class="mt-3 text-sm text-slate-500">Estado</p>
                        <span class="inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">{{ pedido.estado }}</span>
                    </div>
                </div>

                <div class="mx-auto mt-8 flex max-w-2xl flex-col gap-3 sm:flex-row">
                    <a :href="enlaceWhatsapp" target="_blank" rel="noopener" class="boton-secundario flex-1 !border-green-500 !bg-green-600 !text-white hover:!bg-green-700">
                        Enviar por WhatsApp
                    </a>
                    <a href="/catalogo" class="boton-primario flex-1">Seguir comprando</a>
                </div>
            </div>
        </div>
    </Publico>
</template>
