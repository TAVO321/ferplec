<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const flash = computed(() => page.props.flash ?? {});
const tienda = computed(() => page.props.tienda ?? {});
const usuario = computed(() => page.props.auth?.user ?? {});

const abierto = ref(false);
const toast = ref(null);

watch(
    flash,
    (v) => {
        const msg = v.ok ?? v.error;
        if (msg) {
            toast.value = { texto: msg, tipo: v.ok ? 'ok' : 'error' };
            setTimeout(() => (toast.value = null), 3500);
        }
    },
    { immediate: true, deep: true },
);

watch(
    () => page.url,
    () => (abierto.value = false),
);

function salir() {
    router.visit(route('admin.logout'), { method: 'post' });
}

const itemsMenu = [
    { etiqueta: 'Panel', ruta: 'admin.dashboard', icono: 'M4 12h16M4 6h16M4 18h10' },
    { etiqueta: 'Productos', ruta: 'admin.productos.index', icono: 'M20 7H4l1 12h14L20 7zM9 9v4m6-4v4M12 3v4' },
    { etiqueta: 'Catálogo', ruta: 'admin.catalogo', icono: 'M4 5h16v5H4zM4 14h16v5H4z' },
    { etiqueta: 'Apartados', ruta: 'admin.apartados.index', icono: 'M4 9h16M4 9l1 10h14l1-10M9 9V6h6v3' },
    { etiqueta: 'Ajustes', ruta: 'admin.ajustes', icono: 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM19 8l1 4-1.6 1.2 3 2.6-2 3.4-3-.8-1.2 1.6H11.6L10.4 19l-3 .8-2-3.4 3-2.6-1.6-1.2L5 8' },
];

function activo(ruta) {
    return route().current(ruta) || route().current(ruta + '.*');
}
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <div v-if="abierto" class="fixed inset-0 z-40 bg-black/40 lg:hidden" @click="abierto = false" />

        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-black text-white transition-transform lg:translate-x-0"
            :class="{ 'translate-x-0': abierto }"
        >
            <div class="flex h-16 items-center justify-between border-b border-white/10 px-5">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rojo-600 font-display text-xl font-bold">F</span>
                    <div>
                        <p class="titulo-marca text-base font-bold leading-none">FER<span class="text-rojo-500">PLEC</span></p>
                        <p class="text-[11px] text-slate-400">Panel admin</p>
                    </div>
                </div>
                <button class="text-slate-400 hover:text-white lg:hidden" @click="abierto = false" aria-label="Cerrar menú">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path d="M6 6l12 12M6 18L18 6" stroke-linecap="round" /></svg>
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <component
                    v-for="item in itemsMenu"
                    :key="item.etiqueta"
                    :is="item.ruta === 'admin.logout' ? 'a' : Link"
                    :href="item.ruta === 'admin.logout' ? '#' : route(item.ruta)"
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                    :class="activo(item.ruta) ? 'bg-rojo-600 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white'"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" stroke-linecap="round" stroke-linejoin="round">
                        <path :d="item.icono" />
                    </svg>
                    {{ item.etiqueta }}
                </component>
            </nav>

            <div class="border-t border-white/10 px-5 py-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-sm font-bold uppercase">
                        {{ (usuario.nombre || 'A').slice(0, 1) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ usuario.nombre }}</p>
                        <p class="truncate text-xs text-slate-400">{{ usuario.email }}</p>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <button type="button" class="boton-secundario flex-1 !border-white/20 !bg-white/10 !text-white hover:!bg-white/20" @click="salir">
                        Salir
                    </button>
                    <Link href="/" class="boton-secundario flex-1 !border-white/20 !bg-white/10 !text-white hover:!bg-white/20">Ver tienda</Link>
                </div>
            </div>
        </aside>

        <div class="lg:pl-64">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">
                <button class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 lg:hidden" @click="abierto = true" aria-label="Abrir menú">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" /></svg>
                </button>
                <h1 class="text-sm font-semibold text-slate-600 sm:text-base">
                    Panel de administración
                </h1>
                <Link href="/" class="hidden items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-rojo-700 sm:flex">
                    Ver la tienda →
                </Link>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>

        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="-translate-y-4 opacity-0"
            leave-active-class="transition duration-200 ease-in"
            leave-to-class="-translate-y-4 opacity-0"
        >
            <div
                v-if="toast"
                class="fixed right-4 top-20 z-50 max-w-sm rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg"
                :class="toast.tipo === 'ok' ? 'bg-rojo-700' : 'bg-rose-600'"
                role="status"
            >
                {{ toast.texto }}
            </div>
        </Transition>
    </div>
</template>