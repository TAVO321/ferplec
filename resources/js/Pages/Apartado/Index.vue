<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Publico from '../../Layouts/Publico.vue';
import Moneda from '../../Components/Moneda.vue';
import InputError from '../../Components/InputError.vue';

const props = defineProps({
    items: { type: Array, required: true },
    total: { type: String, default: '0' },
    mensaje_whatsapp: { type: String, default: '' },
    whatsapp: { type: String, default: '' },
});

const form = useForm({
    nombre_cliente: '',
    telefono: '',
    direccion: '',
    nota: '',
});

const enlaceWhatsapp = computed(() => {
    if (!props.mensaje_whatsapp) {
        return '#';
    }

    return `https://wa.me/${props.whatsapp}?text=${encodeURIComponent(props.mensaje_whatsapp)}`;
});

function confirmar() {
    form.post(route('apartados.store'), {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Publico>
        <div class="pantalla py-10">
            <p class="text-sm font-semibold uppercase tracking-wide text-rojo-600">Apartar productos</p>
            <h1 class="titulo-marca mt-1 text-3xl font-bold text-slate-900">Confirmá tu pedido</h1>
            <p class="mt-2 max-w-2xl text-slate-500">
                Completá tus datos, guardamos tu apartado en la tienda y podés coordinarlo
                al instante por WhatsApp.
            </p>

            <div v-if="items.length" class="mt-8 grid gap-8 lg:grid-cols-[1fr_380px]">
                <form class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6" @submit.prevent="confirmar">
                    <div>
                        <label for="nombre_cliente" class="etiqueta">Nombre completo *</label>
                        <input id="nombre_cliente" v-model="form.nombre_cliente" type="text" class="campo" placeholder="Ej. Carlos Mamani" :disabled="form.processing">
                        <InputError :mensaje="form.errors.nombre_cliente" />
                    </div>

                    <div>
                        <label for="telefono" class="etiqueta">WhatsApp *</label>
                        <input id="telefono" v-model="form.telefono" type="tel" class="campo" placeholder="Ej. 71234567" :disabled="form.processing">
                        <InputError :mensaje="form.errors.telefono" />
                    </div>

                    <div>
                        <label for="direccion" class="etiqueta">Dirección</label>
                        <input id="direccion" v-model="form.direccion" type="text" class="campo" placeholder="Zona, calle y número" :disabled="form.processing">
                        <InputError :mensaje="form.errors.direccion" />
                    </div>

                    <div>
                        <label for="nota" class="etiqueta">Nota (opcional)</label>
                        <textarea id="nota" v-model="form.nota" rows="3" class="campo" placeholder="Alguna indicación para tu pedido…" :disabled="form.processing" />
                        <InputError :mensaje="form.errors.nota" />
                    </div>

                    <button type="submit" class="boton-primario w-full !py-3 text-base" :disabled="form.processing">
                        {{ form.processing ? 'Registrando…' : 'Confirmar apartado' }}
                    </button>
                    <p class="text-center text-xs text-slate-400">
                        Al confirmar se reservan los productos y se descuenta del stock.
                    </p>
                </form>

                <aside class="h-fit space-y-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Tu pedido</h2>
                        <ul class="mt-4 divide-y divide-slate-100">
                            <li v-for="item in items" :key="item.producto_id" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                                <span class="min-w-0 truncate text-slate-700">
                                    <span class="font-semibold">{{ item.cantidad }}×</span> {{ item.nombre }}
                                </span>
                                <Moneda :valor="item.subtotal" clase="shrink-0 font-semibold text-slate-800" />
                            </li>
                        </ul>
                        <div class="mt-3 flex justify-between border-t border-slate-200 pt-3 text-base font-bold text-slate-900">
                            <span>Total</span>
                            <Moneda :valor="total" />
                        </div>
                    </div>

                    <a :href="enlaceWhatsapp" target="_blank" rel="noopener" class="boton-secundario w-full !border-green-500 !bg-green-600 !text-white hover:!bg-green-700">
                        Enviar por WhatsApp
                    </a>
                </aside>
            </div>

            <div v-else class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center">
                <p class="text-5xl">🧰</p>
                <h2 class="mt-4 text-lg font-semibold text-slate-700">Todavía no tenés productos</h2>
                <p class="mt-1 text-sm text-slate-500">Sumá productos al carrito y volvé para apartarlos.</p>
                <a href="/catalogo" class="boton-primario mt-6">Ir al catálogo</a>
            </div>
        </div>
    </Publico>
</template>