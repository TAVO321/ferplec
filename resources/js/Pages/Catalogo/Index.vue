<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Publico from '../../Layouts/Publico.vue';
import ProductCard from '../../Components/ProductCard.vue';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    areas: { type: Array, required: true },
    productos: { type: Object, required: true },
    filtros: { type: Object, default: () => ({}) },
});

const q = ref(props.filtros.q ?? '');
const area = ref(Number(props.filtros.area) || 0);
const categoria = ref(Number(props.filtros.categoria) || 0);
const orden = ref(props.filtros.orden ?? 'recientes');
const filtrosAbiertos = ref(false);

const areaActiva = computed(() => props.areas.find((a) => a.id === area.value) ?? null);

watch(
    () => props.filtros,
    (nuevos) => {
        q.value = nuevos.q ?? '';
        area.value = Number(nuevos.area) || 0;
        categoria.value = Number(nuevos.categoria) || 0;
        orden.value = nuevos.orden ?? 'recientes';
    },
);

function navegar() {
    router.get(route('catalogo'), {
        q: q.value || undefined,
        area: area.value || undefined,
        categoria: categoria.value || undefined,
        orden: orden.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function elegirArea(id) {
    area.value = id;
    categoria.value = 0;
    navegar();
}

function elegirCategoria(id) {
    categoria.value = id;
    navegar();
}

function restablecer() {
    q.value = '';
    area.value = 0;
    categoria.value = 0;
    orden.value = 'recientes';
    navegar();
}
</script>

<template>
    <Publico>
        <div class="border-b border-slate-200 bg-white">
            <div class="pantalla py-8">
                <p class="text-sm font-semibold uppercase tracking-wide text-rojo-600">Catálogo</p>
                <h1 class="titulo-marca mt-1 text-3xl font-bold text-slate-900">
                    {{ filtros.area_nombre ?? 'Todos nuestros productos' }}
                </h1>
                <p class="mt-2 text-slate-500">
                    {{ filtros.categoria_nombre ? `Categoría: ${filtros.categoria_nombre}` : 'Ferretería, plomería y electricidad domiciliar.' }}
                </p>
            </div>
        </div>

        <div class="pantalla py-8">
            <div class="lg:grid lg:grid-cols-[240px_1fr] lg:gap-8">
                <aside class="mb-6 lg:mb-0">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 lg:hidden"
                        @click="filtrosAbiertos = !filtrosAbiertos"
                    >
                        Filtrar productos
                        <span class="text-slate-400">{{ filtrosAbiertos ? '▲' : '▼' }}</span>
                    </button>

                    <div :class="['mt-3 space-y-6 rounded-2xl border border-slate-200 bg-white p-4 lg:mt-0 lg:block', { hidden: !filtrosAbiertos }]">
                        <div>
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">Áreas</h3>
                            <div class="space-y-2">
                                <button
                                    type="button"
                                    class="block w-full rounded-lg px-3 py-2 text-left text-sm font-medium transition"
                                    :class="area === 0 ? 'bg-rojo-600 text-white' : 'text-slate-600 hover:bg-slate-100'"
                                    @click="elegirArea(0)"
                                >
                                    Todas
                                </button>
                                <button
                                    v-for="a in areas"
                                    :key="a.id"
                                    type="button"
                                    class="block w-full rounded-lg px-3 py-2 text-left text-sm font-medium transition"
                                    :class="area === a.id ? 'bg-rojo-600 text-white' : 'text-slate-600 hover:bg-slate-100'"
                                    @click="elegirArea(a.id)"
                                >
                                    {{ a.nombre }}
                                </button>
                            </div>
                        </div>

                        <div v-if="areaActiva">
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">Categorías</h3>
                            <div class="space-y-2">
                                <button
                                    v-for="c in areaActiva.categorias"
                                    :key="c.id"
                                    type="button"
                                    class="block w-full rounded-lg px-3 py-2 text-left text-sm transition"
                                    :class="categoria === c.id ? 'bg-black text-white' : 'text-slate-600 hover:bg-slate-100'"
                                    @click="elegirCategoria(c.id)"
                                >
                                    {{ c.nombre }}
                                </button>
                            </div>
                        </div>

                        <button type="button" class="text-sm font-semibold text-rojo-600 hover:text-rojo-700" @click="restablecer">
                            Limpiar filtros
                        </button>
                    </div>
                </aside>

                <div>
                    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-slate-500">{{ productos.data.length }} producto(s)</p>
                        <select v-model="orden" class="campo !w-auto" @change="navegar">
                            <option value="recientes">Más recientes</option>
                            <option value="precio-asc">Precio: menor a mayor</option>
                            <option value="precio-desc">Precio: mayor a menor</option>
                            <option value="nombres">Nombre (A–Z)</option>
                        </select>
                    </div>

                    <div v-if="productos.data.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
                        <ProductCard
                            v-for="producto in productos.data"
                            :key="producto.id"
                            :producto="producto"
                        />
                    </div>

                    <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center">
                        <p class="text-lg font-semibold text-slate-700">No encontramos productos</p>
                        <p class="mt-1 text-sm text-slate-500">Probá con otra búsqueda o quitá los filtros.</p>
                        <button type="button" class="boton-secundario mt-5" @click="restablecer">Ver todo</button>
                    </div>

                    <Pagination
                        :paginas="productos.paginas"
                        :pagina-actual="productos.pagina_actual"
                        :preserve-query="{
                            q: q || undefined,
                            area: area || undefined,
                            categoria: categoria || undefined,
                            orden,
                        }"
                    />
                </div>
            </div>
        </div>
    </Publico>
</template>