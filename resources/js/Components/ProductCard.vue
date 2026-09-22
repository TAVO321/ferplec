<script setup>
import { Link } from '@inertiajs/vue3';
import { useCart } from '../composables/useCart';
import Moneda from './Moneda.vue';

const props = defineProps({
    producto: { type: Object, required: true },
});

const cart = useCart();

function agregar() {
    if (props.producto.disponibilidad === 'agotado' && props.producto.stock <= 0) {
        return;
    }

    const resultado = cart.agregar(props.producto, 1);

    if (!resultado.ok) {
        alert(resultado.mensaje);
    }
}
</script>

<template>
    <article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
        <Link
            :href="route('catalogo.show', producto.slug)"
            class="relative block aspect-square overflow-hidden bg-slate-100"
        >
            <img
                v-if="producto.imagen"
                :src="producto.imagen"
                :alt="producto.nombre"
                loading="lazy"
                class="h-full w-full object-cover"
            >
            <div v-else class="flex h-full w-full items-center justify-center">
                <span class="titulo-marca text-3xl font-semibold text-rojo-600">F</span>
            </div>

            <span class="absolute left-2 top-2 rounded-lg bg-black/80 px-2 py-1 text-[11px] font-semibold uppercase tracking-wide text-white">
                {{ producto.area }}
            </span>

            <span
                v-if="producto.precio_anterior"
                class="absolute right-2 top-2 rounded-lg bg-red-100 px-2 py-1 text-[11px] font-bold uppercase text-rojo-700"
            >
                Oferta
            </span>
        </Link>

        <div class="flex flex-1 flex-col gap-2 p-3">
            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                {{ producto.categoria }}
            </p>
            <Link :href="route('catalogo.show', producto.slug)" class="line-clamp-2 text-sm font-semibold text-slate-800 hover:text-rojo-700">
                {{ producto.nombre }}
            </Link>
            <p v-if="producto.marca" class="text-xs text-slate-500">{{ producto.marca }}</p>

            <div class="mt-auto flex items-end justify-between gap-2 pt-2">
                <div>
                    <p v-if="producto.precio_anterior" class="text-xs text-slate-400 line-through">
                        <Moneda :valor="producto.precio_anterior" />
                    </p>
                    <Moneda
                        :valor="producto.precio"
                        clase="text-lg font-bold text-slate-900"
                    />
                </div>
                <button
                    type="button"
                    class="boton-primario !px-3 !py-2 text-xs"
                    :disabled="(producto.disponibilidad === 'agotado' && producto.stock <= 0)"
                    @click="agregar"
                >
                    <span v-if="producto.disponibilidad === 'agotado' && producto.stock <= 0">Agotado</span>
                    <span v-else-if="producto.disponibilidad === 'bajo_pedido'">Pedir</span>
                    <span v-else>
                        <span class="sr-only">Agregar</span>+
                    </span>
                </button>
            </div>
        </div>
    </article>
</template>
