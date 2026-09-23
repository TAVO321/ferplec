<script setup>
import { computed, inject, ref } from 'vue';
import { useCart } from '../../composables/useCart';
import Publico from '../../Layouts/Publico.vue';
import ProductCard from '../../Components/ProductCard.vue';
import Moneda from '../../Components/Moneda.vue';

const mostrarToastCart = inject('mostrarToastCart', null);

const props = defineProps({
    producto: { type: Object, required: true },
    relacionados: { type: Array, default: () => [] },
});

const cart = useCart();
const seleccionada = ref(0);
const cantidad = ref(1);

const imagenes = computed(() => props.producto.imagenes ?? []);
const imagenActual = computed(() => imagenes.value[seleccionada.value] ?? null);

function agregar() {
    if (props.producto.disponibilidad === 'agotado' && props.producto.stock <= 0) {
        return;
    }

    const resultado = cart.agregar(props.producto, cantidad.value);

    if (!resultado.ok) {
        alert(resultado.mensaje);
        return;
    }

    if (mostrarToastCart) {
        mostrarToastCart(`Agregado (${cantidad.value}) al carrito`);
    }

    cantidad.value = 1;
}
</script>

<template>
    <Publico>
        <div class="pantalla py-8">
            <nav class="mb-6 text-sm text-slate-500">
                <a href="/" class="hover:text-rojo-600">Inicio</a>
                <span class="mx-2">/</span>
                <a href="/catalogo" class="hover:text-rojo-600">Catalogo</a>
                <span class="mx-2">/</span>
                <span class="text-slate-700">{{ producto.categoria.nombre }}</span>
            </nav>

            <div class="grid gap-8 lg:grid-cols-2">
                <div>
                    <div class="relative aspect-square overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <img
                            v-if="imagenActual"
                            :src="imagenActual.full"
                            :alt="producto.nombre"
                            loading="lazy"
                            class="h-full w-full object-cover"
                        >
                        <div v-else class="flex h-full w-full flex-col items-center justify-center bg-slate-100">
                            <span class="titulo-marca text-7xl font-bold text-rojo-600">F</span>
                            <span class="mt-2 text-sm text-slate-400">Sin imagen disponible</span>
                        </div>

                        <span
                            v-if="producto.precio_anterior"
                            class="absolute left-3 top-3 rounded-lg bg-rojo-600 px-2.5 py-1 text-xs font-bold uppercase text-white shadow"
                        >
                            Oferta
                        </span>
                    </div>

                    <div v-if="imagenes.length > 1" class="mt-3 grid grid-cols-4 gap-2 sm:grid-cols-5">
                        <button
                            v-for="(img, i) in imagenes"
                            :key="img.uid"
                            type="button"
                            class="overflow-hidden rounded-xl border-2 transition"
                            :class="i === seleccionada ? 'border-rojo-600' : 'border-transparent hover:border-slate-300'"
                            @click="seleccionada = i"
                        >
                            <img :src="img.thumb" :alt="`Imagen ${i + 1}`" loading="lazy" class="aspect-square w-full object-cover">
                        </button>
                    </div>
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-lg bg-black px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-white">
                            {{ producto.categoria.area }}
                        </span>
                        <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                            {{ producto.categoria.nombre }}
                        </span>
                        <span v-if="producto.marca" class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                            {{ producto.marca }}
                        </span>
                        <span v-if="producto.codigo" class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-mono text-slate-500">
                            {{ producto.codigo }}
                        </span>
                    </div>

                    <h1 class="titulo-marca mt-4 text-3xl font-bold text-slate-900 sm:text-4xl">
                        {{ producto.nombre }}
                    </h1>

                    <div class="mt-5 flex items-end gap-3">
                        <Moneda :valor="producto.precio" clase="text-3xl font-bold text-slate-900" />
                        <Moneda v-if="producto.precio_anterior" :valor="producto.precio_anterior" clase="text-xl text-slate-400 line-through" />
                    </div>

                    <p
                        class="mt-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold"
                        :class="{
                            'bg-rose-50 text-rose-600': producto.disponibilidad === 'agotado',
                            'bg-amber-50 text-amber-700': producto.disponibilidad === 'bajo_pedido',
                            'bg-green-50 text-green-700': producto.disponibilidad === 'disponible',
                        }"
                    >
                        <span
                            class="h-2 w-2 rounded-full"
                            :class="{
                                'bg-rose-500': producto.disponibilidad === 'agotado',
                                'bg-amber-500': producto.disponibilidad === 'bajo_pedido',
                                'bg-green-500': producto.disponibilidad === 'disponible',
                            }"
                        />
                        <span v-if="producto.disponibilidad === 'agotado'">Agotado</span>
                        <span v-else-if="producto.disponibilidad === 'bajo_pedido'">Bajo pedido</span>
                        <span v-else>En stock ({{ producto.estoque }} {{ producto.unidad_de_medida }})</span>
                    </p>

                    <p v-if="producto.descripcion" class="mt-5 text-slate-600">
                        {{ producto.descripcion }}
                    </p>

                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        <div class="flex items-center rounded-xl border border-slate-200 bg-white">
                            <button
                                type="button"
                                class="flex h-11 w-11 items-center justify-center text-xl text-slate-500 transition hover:text-rojo-600 disabled:opacity-40"
                                :disabled="cantidad <= 1"
                                @click="cantidad = Math.max(1, cantidad - 1)"
                            >
                                −
                            </button>
                            <span class="w-10 text-center font-semibold">{{ cantidad }}</span>
                            <button
                                type="button"
                                class="flex h-11 w-11 items-center justify-center text-xl text-slate-500 transition hover:text-rojo-600 disabled:opacity-40"
                                :disabled="producto.disponibilidad === 'agotado' || cantidad >= 99"
                                @click="cantidad = Math.min(99, cantidad + 1)"
                            >
                                +
                            </button>
                        </div>

                        <button
                            type="button"
                            class="boton-primario h-11 !px-8 text-base"
                            :disabled="(producto.disponibilidad === 'agotado' && producto.agotado)"
                            @click="agregar"
                        >
                            <span v-if="producto.disponibilidad === 'bajo_pedido'">Solicitar cotizacion</span>
                            <span v-else>Agregar al carrito</span>
                        </button>
                    </div>

                    <div v-if="producto.atributos.length" class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-600">Caracteristicas</h2>
                        </div>
                        <dl class="divide-y divide-slate-100">
                            <div v-for="atributo in producto.atributos" :key="atributo.etiqueta" class="grid grid-cols-2 gap-2 px-4 py-3">
                                <dt class="text-sm font-medium text-slate-500">{{ atributo.etiqueta }}</dt>
                                <dd class="text-right text-sm font-semibold text-slate-800">
                                    {{ atributo.valor }} <span v-if="atributo.unidad" class="text-slate-400">{{ atributo.unidad }}</span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <section v-if="relacionados.length" class="mt-14">
                <h2 class="titulo-marca text-2xl font-bold text-slate-900">Tambien te puede interesar</h2>
                <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <ProductCard
                        v-for="producto in relacionados"
                        :key="producto.id"
                        :producto="producto"
                    />
                </div>
            </section>
        </div>
    </Publico>
</template>
