<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Publico from '../../Layouts/Publico.vue';
import Moneda from '../../Components/Moneda.vue';
import { useCart } from '../../composables/useCart';

const cart = useCart();
const page = usePage();
const whatsapp = computed(() => (page.props.tienda?.whatsapp ?? '').replace(/\D/g, ''));

function cambiarCantidad(productoId, cantidad) {
    cart.actualizar(productoId, cantidad);
}

function quitar(productoId) {
    cart.quitar(productoId);
}

function mensajeWhatsApp() {
    const tienda = page.props.tienda?.nombre ?? 'FERPLEC';
    let texto = `Hola ${tienda}, quiero hacer un pedido:\n\n`;

    cart.items.value.forEach((item, i) => {
        texto += `${i + 1}. ${item.nombre}`;
        if (item.marca) {
            texto += ` (${item.marca})`;
        }
        texto += `\n`;
        texto += `   Cantidad: ${item.cantidad} ${item.unidad_de_medida}\n`;
        texto += `   Precio unitario: Bs ${Number(item.precio).toFixed(2)}\n`;
        texto += `   Subtotal: Bs ${(item.precio * item.cantidad).toFixed(2)}\n\n`;
    });

    texto += `*Total estimado: Bs ${cart.total.value.toFixed(2)}*\n\n`;
    texto += 'Por favor confirmame disponibilidad y forma de pago/entrega. Gracias.';

    return encodeURIComponent(texto);
}

function urlWhatsApp() {
    if (!whatsapp.value) {
        return '#';
    }
    return `https://wa.me/${whatsapp.value}?text=${mensajeWhatsApp()}`;
}
</script>

<template>
    <Publico>
        <div class="pantalla py-6 sm:py-10">
            <h1 class="titulo-marca text-2xl font-bold text-slate-900 sm:text-3xl">Tu carrito</h1>
            <p class="mt-1 text-sm text-slate-500">Se guarda por 24 horas en este dispositivo.</p>

            <div v-if="cart.items.value.length" class="mt-6 grid gap-6 lg:mt-8 lg:grid-cols-[1fr_320px]">
                <div class="space-y-3">
                    <div
                        v-for="item in cart.items.value"
                        :key="item.producto_id"
                        class="rounded-2xl border border-slate-200 bg-white p-3 sm:p-4"
                    >
                        <div class="flex gap-3 sm:gap-4">
                            <a :href="route('catalogo.show', item.slug)" class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 sm:h-20 sm:w-20">
                                <img v-if="item.imagen" :src="item.imagen" :alt="item.nombre" loading="lazy" class="h-full w-full object-cover">
                                <span v-else class="titulo-marca text-2xl font-bold text-rojo-600">F</span>
                            </a>

                            <div class="min-w-0 flex-1">
                                <a :href="route('catalogo.show', item.slug)" class="block truncate text-sm font-semibold text-slate-800 hover:text-rojo-700">
                                    {{ item.nombre }}
                                </a>
                                <p class="text-xs text-slate-500">{{ item.marca }} · {{ item.unidad_de_medida }}</p>

                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <Moneda v-if="item.precio_anterior" :valor="item.precio_anterior" clase="text-xs text-slate-400 line-through" />
                                    <Moneda :valor="item.precio" clase="text-sm font-bold text-slate-900" />
                                </div>
                            </div>

                            <button
                                type="button"
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                                :aria-label="`Quitar ${item.nombre}`"
                                @click="quitar(item.producto_id)"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M19 7l-.9 12.1A2 2 0 0116.1 21H7.9a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </button>
                        </div>

                        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                            <div class="flex items-center rounded-xl border border-slate-200">
                                <button type="button" class="flex h-9 w-9 items-center justify-center text-lg text-slate-500 hover:text-rojo-600" @click="cambiarCantidad(item.producto_id, item.cantidad - 1)">−</button>
                                <span class="w-9 text-center text-sm font-semibold">{{ item.cantidad }}</span>
                                <button type="button" class="flex h-9 w-9 items-center justify-center text-lg text-slate-500 hover:text-rojo-600 disabled:opacity-40" :disabled="item.cantidad >= 99" @click="cambiarCantidad(item.producto_id, item.cantidad + 1)">+</button>
                            </div>
                            <Moneda :valor="(item.precio * item.cantidad).toFixed(2)" clase="text-base font-bold text-slate-900" />
                        </div>
                    </div>
                </div>

                <div class="h-fit rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Resumen</h2>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-slate-500">
                            <dt>Productos</dt>
                            <dd>{{ cart.items.value.length }}</dd>
                        </div>
                        <div class="flex justify-between border-t border-slate-100 pt-3 text-base font-bold text-slate-900">
                            <dt>Total estimado</dt>
                            <dd><Moneda :valor="cart.total.value.toFixed(2)" /></dd>
                        </div>
                    </dl>

                    <a
                        :href="urlWhatsApp()"
                        target="_blank"
                        rel="noopener"
                        class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700"
                        :class="{ 'pointer-events-none opacity-50': !whatsapp }"
                    >
                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                        Enviar pedido por WhatsApp
                    </a>

                    <p v-if="!whatsapp" class="mt-2 text-xs text-amber-600">Falta configurar el numero de WhatsApp en ajustes.</p>

                    <Link href="/catalogo" class="boton-secundario mt-2 flex w-full items-center justify-center">Seguir comprando</Link>
                    <button type="button" class="mt-2 w-full text-center text-sm text-rose-600 hover:text-rose-700" @click="cart.limpiar()">Vaciar carrito</button>
                </div>
            </div>

            <div v-else class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center">
                <p class="text-5xl">🛒</p>
                <h2 class="mt-4 text-lg font-semibold text-slate-700">Tu carrito esta vacio</h2>
                <p class="mt-1 text-sm text-slate-500">Agrega productos del catalogo para enviar tu pedido.</p>
                <a href="/catalogo" class="boton-primario mt-6">Ver catalogo</a>
            </div>
        </div>
    </Publico>
</template>
