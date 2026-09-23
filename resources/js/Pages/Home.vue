<script setup>
import Publico from '../Layouts/Publico.vue';
import IconoArea from '../Components/IconoArea.vue';
import ProductCard from '../Components/ProductCard.vue';

defineProps({
    areas: { type: Array, default: () => [] },
    destacados: { type: Array, default: () => [] },
    nuevos: { type: Array, default: () => [] },
});
</script>

<template>
    <Publico>
        <section class="relative overflow-hidden bg-black text-white">
            <div class="pointer-events-none absolute -right-40 -top-40 hidden h-96 w-96 rounded-full bg-rojo-700/30 blur-3xl md:block" />
            <div class="pointer-events-none absolute -bottom-32 -left-32 hidden h-80 w-80 rounded-full bg-rojo-900/40 blur-3xl md:block" />

            <div class="pantalla relative grid items-center gap-10 py-16 lg:grid-cols-2 lg:py-24">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full border border-rojo-500/40 bg-rojo-600/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-rojo-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-rojo-500" />
                        Ferretería · Plomería · Electricidad
                    </p>
                    <h1 class="titulo-marca mt-5 text-3xl font-bold leading-tight sm:text-5xl lg:text-6xl">
                        Todo para tu <span class="text-rojo-500">proyecto</span>, en un solo lugar
                    </h1>
                    <p class="mt-4 max-w-xl text-base text-slate-300 sm:mt-5 sm:text-lg">
                        FERPLEC es tu ferretería de confianza: herramientas, tuberías, cables,
                        grifería y accesorios para el hogar y la instalación remunerativa.
                    </p>
                    <p class="mt-2 text-sm text-slate-400">
                        Agregá productos al carrito y envianos tu pedido por WhatsApp.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3 sm:mt-8">
                        <a href="/catalogo" class="boton-primario !px-5 !py-2.5 text-sm sm:!px-7 sm:!py-3 sm:text-base">Ver catálogo</a>
                    </div>
                </div>

                <div class="relative">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
                        <a
                            v-for="area in areas"
                            :key="area.id"
                            :href="'/catalogo?area=' + area.id"
                            class="group rounded-2xl bg-white/5 p-4 text-center transition hover:bg-rojo-600 sm:p-5"
                        >
                            <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-rojo-600 text-white shadow-lg sm:h-14 sm:w-14 sm:rounded-2xl">
                                <IconoArea :nombre="area.icono" clase="h-5 w-5 sm:h-7 sm:w-7" />
                            </span>
                            <p class="mt-2 text-sm font-bold sm:mt-3">{{ area.nombre }}</p>
                            <p class="text-xs text-slate-400 group-hover:text-rojo-100">{{ area.cantidad_categorias }} categorias</p>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="destacados.length" class="py-14">
            <div class="pantalla">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-rojo-600">Lo más pedido</p>
                        <h2 class="titulo-marca mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">Productos destacados</h2>
                    </div>
                    <a href="/catalogo" class="boton-secundario shrink-0">Ver todos</a>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <ProductCard
                        v-for="producto in destacados"
                        :key="producto.id"
                        :producto="producto"
                    />
                </div>
            </div>
        </section>

        <section v-if="nuevos.length" class="bg-white py-14">
            <div class="pantalla">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-rojo-600">Recién llegados</p>
                        <h2 class="titulo-marca mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">Novedades en catálogo</h2>
                    </div>
                    <a href="/catalogo" class="boton-secundario shrink-0">Ver todos</a>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <ProductCard
                        v-for="producto in nuevos"
                        :key="producto.id"
                        :producto="producto"
                    />
                </div>
            </div>
        </section>

        <section class="bg-black py-12 text-white">
            <div class="pantalla grid gap-8 text-center sm:grid-cols-3">
                <div>
                    <p class="titulo-marca text-3xl font-bold text-rojo-500">3</p>
                    <p class="mt-1 text-sm text-slate-300">áreas especializadas</p>
                </div>
                <div>
                    <p class="titulo-marca text-3xl font-bold text-rojo-500">100%</p>
                    <p class="mt-1 text-sm text-slate-300">productos para el hogar</p>
                </div>
                <div>
                    <p class="titulo-marca text-3xl font-bold text-rojo-500">WhatsApp</p>
                    <p class="mt-1 text-sm text-slate-300">pedidos coordinados al instante</p>
                </div>
            </div>
        </section>
    </Publico>
</template>