<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Publico from '../../Layouts/Publico.vue';
import Moneda from '../../Components/Moneda.vue';
import InputError from '../../Components/InputError.vue';
import { useCart } from '../../composables/useCart';

const props = defineProps({
    whatsapp: { type: String, default: '' },
});

const cart = useCart();

const form = useForm({
    nombre_cliente: '',
    telefono: '',
    direccion: '',
    ubicacion: '',
    nota: '',
    items: [],
});

const mensajeWhatsapp = computed(() => {
    const moneda = 'Bs';
    const lineas = ['Hola FERPLEC, quiero apartar estos productos:'];

    for (const item of cart.items.value) {
        lineas.push(`- ${item.cantidad}x ${item.nombre} (${moneda} ${item.precio})`);
    }

    lineas.push(`Total: ${moneda} ${cart.total.value.toFixed(2)}`);
    lineas.push('Confirmo que pagare al contado y adelantado.');

    return lineas.join('\n');
});

const enlaceWhatsapp = computed(() => {
    if (!props.whatsapp) {
        return '#';
    }

    return `https://wa.me/${props.whatsapp}?text=${encodeURIComponent(mensajeWhatsapp.value)}`;
});

function confirmar() {
    if (cart.items.value.length === 0) {
        form.setError('items', 'Tu carrito esta vacio.');
        return;
    }

    form.items = cart.paraBackend();
    form.post(route('apartados.store'), {
        onSuccess: () => {
            cart.limpiar();
            form.reset();
        },
    });
}
</script>

<template>
    <Publico>
        <div class="pantalla py-10">
            <p class="text-sm font-semibold uppercase tracking-wide text-rojo-600">Apartar productos</p>
            <h1 class="titulo-marca mt-1 text-3xl font-bold text-slate-900">Confirma tu pedido</h1>
            <p class="mt-2 max-w-2xl text-slate-500">
                Completa tus datos. Te contactaremos por WhatsApp para confirmar. Pago al contado y adelantado.
            </p>

            <div v-if="cart.items.value.length" class="mt-8 grid gap-8 lg:grid-cols-[1fr_380px]">
                <form class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6" @submit.prevent="confirmar">
                    <div class="rounded-xl bg-amber-50 p-4 text-sm text-amber-800">
                        <p class="font-semibold">Importante</p>
                        <p>El apartado esta sujeto a confirmacion. El pago se realiza al contado y adelantado.</p>
                    </div>

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
                        <label for="direccion" class="etiqueta">Direccion</label>
                        <input id="direccion" v-model="form.direccion" type="text" class="campo" placeholder="Zona, calle y numero" :disabled="form.processing">
                        <InputError :mensaje="form.errors.direccion" />
                    </div>

                    <div>
                        <label for="ubicacion" class="etiqueta">Ubicacion aproximada</label>
                        <input id="ubicacion" v-model="form.ubicacion" type="text" class="campo" placeholder="Ej. cerca del mercado central" :disabled="form.processing">
                        <InputError :mensaje="form.errors.ubicacion" />
                    </div>

                    <div>
                        <label for="nota" class="etiqueta">Nota (opcional)</label>
                        <textarea id="nota" v-model="form.nota" rows="3" class="campo" placeholder="Alguna indicacion para tu pedido..." :disabled="form.processing" />
                        <InputError :mensaje="form.errors.nota" />
                    </div>

                    <InputError :mensaje="form.errors.items" />

                    <button type="submit" class="boton-primario w-full !py-3 text-base" :disabled="form.processing">
                        {{ form.processing ? 'Registrando...' : 'Confirmar apartado' }}
                    </button>
                </form>

                <aside class="h-fit space-y-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Tu pedido</h2>
                        <ul class="mt-4 divide-y divide-slate-100">
                            <li v-for="item in cart.items.value" :key="item.producto_id" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                                <span class="min-w-0 truncate text-slate-700">
                                    <span class="font-semibold">{{ item.cantidad }}x</span> {{ item.nombre }}
                                </span>
                                <Moneda :valor="(item.precio * item.cantidad).toFixed(2)" clase="shrink-0 font-semibold text-slate-800" />
                            </li>
                        </ul>
                        <div class="mt-3 flex justify-between border-t border-slate-200 pt-3 text-base font-bold text-slate-900">
                            <span>Total</span>
                            <Moneda :valor="cart.total.value.toFixed(2)" />
                        </div>
                    </div>

                    <a :href="enlaceWhatsapp" target="_blank" rel="noopener" class="boton-secundario w-full !border-green-500 !bg-green-600 !text-white hover:!bg-green-700">
                        Enviar por WhatsApp
                    </a>
                </aside>
            </div>

            <div v-else class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center">
                <p class="text-5xl">🧰</p>
                <h2 class="mt-4 text-lg font-semibold text-slate-700">Todavia no tenes productos</h2>
                <p class="mt-1 text-sm text-slate-500">Agrega productos al carrito y volve para apartarlos.</p>
                <a href="/catalogo" class="boton-primario mt-6">Ir al catalogo</a>
            </div>
        </div>
    </Publico>
</template>
