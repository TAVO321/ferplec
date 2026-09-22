<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import IconoArea from '../Components/IconoArea.vue';
import { useCart } from '../composables/useCart';

const page = usePage();
const tienda = computed(() => page.props.tienda ?? {});
const cart = useCart();
const carritoCount = computed(() => cart.contar.value);
const flash = computed(() => page.props.flash ?? {});

const menuAbierto = ref(false);
const busqueda = ref('');
const toast = ref(null);

watch(
    flash,
    (valor) => {
        const msg = valor.ok ?? valor.error;
        if (msg) {
            toast.value = { texto: msg, tipo: valor.ok ? 'ok' : 'error' };
            setTimeout(() => (toast.value = null), 3500);
        }
    },
    { immediate: true, deep: true },
);

function buscar() {
    const q = busqueda.value.trim();
    if (q) {
        router.get(route('catalogo'), { q }, { preserveState: false });
    }
}
</script>

<template>
    <div class="flex min-h-screen flex-col bg-marfil">
        <header class="sticky top-0 z-40 border-b border-black/10 bg-black text-white shadow-md">
            <div class="pantalla flex h-16 items-center justify-between gap-4">
                <Link href="/" class="flex items-center gap-2.5" @click="menuAbierto = false">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rojo-600 font-display text-xl font-bold text-white">
                        F
                    </span>
                    <span class="titulo-marca text-xl font-bold leading-none tracking-tight">
                        FER<span class="text-rojo-500">PLEC</span>
                    </span>
                </Link>

                <nav class="hidden items-center gap-1 md:flex">
                    <Link href="/" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 hover:text-white">Inicio</Link>
                    <Link href="/catalogo" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 hover:text-white">Catalogo</Link>
                    <Link href="/apartado" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 hover:text-white">Apartar</Link>
                    <Link href="/pedido-especial" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 hover:text-white">Pedido especial</Link>
                </nav>

                <div class="hidden flex-1 justify-center px-4 md:flex">
                    <form class="w-full max-w-md" @submit.prevent="buscar">
                        <label class="relative block">
                            <span class="sr-only">Buscar productos</span>
                            <input
                                v-model="busqueda"
                                type="search"
                                placeholder="Buscar productos…"
                                class="w-full rounded-full border-transparent bg-white/10 py-2 pl-4 pr-10 text-sm text-white placeholder:text-slate-400 focus:border-transparent focus:bg-white/15 focus:ring-2 focus:ring-rojo-500"
                            >
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-300">🔍</span>
                        </label>
                    </form>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        href="/carrito"
                        class="relative flex h-10 w-10 items-center justify-center rounded-xl text-slate-200 transition hover:bg-white/10 hover:text-white"
                        aria-label="Carrito"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                            <path d="M4.6 5h1l1.4 8M7.4 13h9.8l1.8-6H6M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2zM17 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2z" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span
                            v-if="carritoCount > 0"
                            class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rojo-600 px-1 text-[11px] font-bold text-white"
                        >
                            {{ carritoCount }}
                        </span>
                    </Link>

                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-200 md:hidden"
                        :aria-expanded="menuAbierto"
                        @click="menuAbierto = !menuAbierto"
                    >
                        <svg v-if="!menuAbierto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                        </svg>
                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                            <path d="M6 6l12 12M6 18L18 6" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
            </div>

            <div v-if="menuAbierto" class="border-t border-white/10 bg-black px-4 pb-4 pt-3 md:hidden">
                <form class="mb-3" @submit.prevent="buscar">
                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar productos…"
                        class="campo !border-white/20 !bg-white/10 !text-white placeholder:!text-slate-400"
                    >
                </form>
                <nav class="flex flex-col gap-1">
                    <Link href="/" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-200 hover:bg-white/10" @click="menuAbierto = false">Inicio</Link>
                    <Link href="/catalogo" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-200 hover:bg-white/10" @click="menuAbierto = false">Catalogo</Link>
                    <Link href="/apartado" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-200 hover:bg-white/10" @click="menuAbierto = false">Apartar productos</Link>
                    <Link href="/pedido-especial" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-200 hover:bg-white/10" @click="menuAbierto = false">Pedido especial</Link>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t border-black/10 bg-black text-white">
            <div class="pantalla grid gap-8 py-10 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rojo-600 font-display text-xl font-bold text-white">F</span>
                        <span class="titulo-marca text-xl font-bold">FER<span class="text-rojo-500">PLEC</span></span>
                    </div>
                    <p class="mt-3 max-w-sm text-sm text-slate-400">
                        Ferretería, plomería y electricidad domiciliar. Todo lo que necesitás para tu proyecto, en un solo lugar.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-300">Catálogo</h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><Link href="/catalogo" class="text-slate-400 hover:text-white">Todos los productos</Link></li>
                        <li><Link href="/apartado" class="text-slate-400 hover:text-white">Cómo apartar</Link></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-300">Contacto</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-400">
                        <li v-if="tienda.whatsapp" class="flex items-center gap-2">
                            <a :href="'https://wa.me/' + tienda.whatsapp" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-slate-400 hover:text-white">
                                WhatsApp: {{ tienda.whatsapp }}
                            </a>
                        </li>
                        <li v-if="tienda.direccion">{{ tienda.direccion }}</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-white/10 py-4">
                <div class="pantalla flex flex-col items-center justify-between gap-2 text-xs text-slate-500 sm:flex-row">
                    <span>© {{ new Date().getFullYear() }} {{ tienda.nombre }}. Todos los derechos reservados.</span>
                    <Link href="/acceso" class="hover:text-slate-300">Acceso administrador</Link>
                </div>
            </div>
        </footer>

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