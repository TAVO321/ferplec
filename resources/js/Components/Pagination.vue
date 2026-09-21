<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    paginas: { type: Number, required: true },
    paginaActual: { type: Number, required: true },
    ruta: { type: String, default: 'catalogo' },
    preserveQuery: { type: Object, default: () => ({}) },
});

const numeros = Array.from({ length: props.paginas }, (_, i) => i + 1);

function ir(pagina) {
    if (pagina < 1 || pagina > props.paginas) {
        return;
    }
    router.get(route(props.ruta), { ...props.preserveQuery, page: pagina }, { preserveState: true });
}
</script>

<template>
    <nav v-if="paginas > 1" class="mt-10 flex items-center justify-center gap-2">
        <button
            type="button"
            class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-rojo-500 hover:text-rojo-600 disabled:opacity-40"
            :disabled="paginaActual <= 1"
            @click="ir(paginaActual - 1)"
        >
            ‹
        </button>

        <button
            v-for="n in numeros"
            :key="n"
            type="button"
            class="h-9 rounded-lg px-3 text-sm font-medium transition"
            :class="n === paginaActual ? 'bg-rojo-600 text-white' : 'border border-slate-200 text-slate-600 hover:border-rojo-500 hover:text-rojo-600'"
            @click="ir(n)"
        >
            {{ n }}
        </button>

        <button
            type="button"
            class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-rojo-500 hover:text-rojo-600 disabled:opacity-40"
            :disabled="paginaActual >= paginas"
            @click="ir(paginaActual + 1)"
        >
            ›
        </button>
    </nav>
</template>