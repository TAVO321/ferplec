<script setup>
import { computed, ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import InputError from '../../../Components/InputError.vue';

const props = defineProps({
    areas: { type: Array, required: true },
    tab: { type: String, default: 'areas' },
});

const ICONOS = ['herramientas', 'llave_agua', 'rayo', 'tienda', 'camion', 'escudo'];

const tabActivo = computed(() => (props.tab === 'categorias' ? 'categorias' : 'areas'));

function cambiarTab(nuevo) {
    router.get(route('admin.catalogo'), { tab: nuevo }, { preserveState: true, replace: true });
}

// ---------- Áreas ----------
const nuevaArea = useForm({ nombre: '', icono: 'tienda', color: '', descripcion: '' });

const areaEditandoId = ref(null);
const areaEditando = useForm({ nombre: '', icono: '', color: '', descripcion: '', activa: true });

function abrirArea(area) {
    areaEditandoId.value = area.id;
    areaEditando.clearErrors();
    areaEditando.nombre = area.nombre;
    areaEditando.icono = area.icono || 'tienda';
    areaEditando.color = area.color || '#d9232d';
    areaEditando.descripcion = area.descripcion || '';
    areaEditando.activa = area.activa;
}

function crearArea() {
    nuevaArea.post(route('admin.catalog.areas.store'), {
        preserveScroll: true,
        onSuccess: () => nuevaArea.reset('nombre', 'icono', 'color', 'descripcion'),
    });
}

function actualizarArea() {
    areaEditando.patch(route('admin.catalog.areas.update', areaEditandoId.value), {
        preserveScroll: true,
        onSuccess: () => (areaEditandoId.value = null),
    });
}

function eliminarArea(area) {
    if (!window.confirm(`¿Eliminar el área "${area.nombre}"?`)) {
        return;
    }
    router.delete(route('admin.catalog.areas.destroy', area.id), {
        preserveScroll: true,
        onError: (err) => window.alert(Object.values(err).join('\n')),
    });
}

// ---------- Categorías ----------
const mostrandoNueva = ref(false);
const categoriaEditandoId = ref(null);
const categoriaForm = useForm({
    area_id: '',
    nombre: '',
    descripcion: '',
    activa: true,
    campos: [],
});

const plantillaCampo = () => ({
    nombre: '',
    etiqueta: '',
    tipo: 'texto',
    unidad: '',
    obligatorio: false,
    opciones: '',
});

function prepararFormNueva() {
    categoriaEditandoId.value = null;
    mostrandoNueva.value = true;
    categoriaForm.clearErrors().reset();
    categoriaForm.area_id = props.areas[0]?.id ?? '';
    categoriaForm.campos = [plantillaCampo()];
}

function abrirCategoria(categoria, areaId) {
    mostrandoNueva.value = false;
    categoriaEditandoId.value = categoria.id;
    categoriaForm.clearErrors().reset();
    categoriaForm.area_id = areaId;
    categoriaForm.nombre = categoria.nombre;
    categoriaForm.descripcion = categoria.descripcion || '';
    categoriaForm.activa = categoria.activa;
    categoriaForm.campos = categoria.campos.map((c) => ({
        nombre: c.nombre,
        etiqueta: c.etiqueta || '',
        tipo: c.tipo,
        unidad: c.unidad || '',
        obligatorio: c.obligatorio,
        opciones: Array.isArray(c.opciones) ? c.opciones.join('\n') : '',
    }));
}

function agregarCampo() {
    categoriaForm.campos.push(plantillaCampo());
}

function quitarCampo(indice) {
    categoriaForm.campos.splice(indice, 1);
}

function prepararCampos() {
    return categoriaForm.campos.map((c) => ({
        ...c,
        opciones: c.tipo === 'seleccion'
            ? c.opciones.split('\n').map((s) => s.trim()).filter(Boolean)
            : null,
        unidad: c.unidad || null,
        etiqueta: c.etiqueta || null,
    }));
}

function guardarCategoria() {
    const datos = {
        ...categoriaForm.data(),
        campos: prepararCampos(),
    };

    if (categoriaEditandoId.value) {
        router.patch(route('admin.catalog.categorias.update', categoriaEditandoId.value), datos, {
            preserveScroll: true,
            onSuccess: () => (categoriaEditandoId.value = null),
            onError: (err) => (categoriaForm.errors = err),
        });
    } else {
        router.post(route('admin.catalog.categorias.store'), datos, {
            preserveScroll: true,
            onSuccess: () => prepararFormNueva(),
            onError: (err) => (categoriaForm.errors = err),
        });
    }
}

function eliminarCategoria(categoria) {
    if (!window.confirm(`¿Eliminar la categoría "${categoria.nombre}"?`)) {
        return;
    }
    router.delete(route('admin.catalog.categorias.destroy', categoria.id), {
        preserveScroll: true,
        onError: (err) => window.alert(Object.values(err).join('\n')),
    });
}
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-4xl space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Catálogo</h2>
                    <p class="text-sm text-slate-500">Áreas, categorías y los campos de cada categoría.</p>
                </div>
                <div class="flex rounded-xl border border-slate-200 bg-white p-1">
                    <button
                        type="button"
                        class="rounded-lg px-4 py-1.5 text-sm font-semibold transition"
                        :class="tabActivo === 'areas' ? 'bg-black text-white' : 'text-slate-500 hover:text-slate-800'"
                        @click="cambiarTab('areas')"
                    >
                        Áreas
                    </button>
                    <button
                        type="button"
                        class="rounded-lg px-4 py-1.5 text-sm font-semibold transition"
                        :class="tabActivo === 'categorias' ? 'bg-black text-white' : 'text-slate-500 hover:text-slate-800'"
                        @click="cambiarTab('categorias')"
                    >
                        Categorías
                    </button>
                </div>
            </div>

            <!-- ===================== ÁREAS ===================== -->
            <div v-if="tabActivo === 'areas'" class="space-y-6">
                <form class="rounded-2xl border border-slate-200 bg-white p-5" @submit.prevent="crearArea">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-600">Nueva área</h3>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="etiqueta">Nombre *</label>
                            <input v-model="nuevaArea.nombre" type="text" class="campo" placeholder="Ej. Electromateriales">
                            <InputError :mensaje="nuevaArea.errors.nombre" />
                        </div>
                        <div>
                            <label class="etiqueta">Icono</label>
                            <select v-model="nuevaArea.icono" class="campo">
                                <option v-for="i in ICONOS" :key="i" :value="i">{{ i }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="etiqueta">Descripción</label>
                            <input v-model="nuevaArea.descripcion" type="text" class="campo" placeholder="Breve descripción">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="boton-primario" :disabled="nuevaArea.processing">Crear área</button>
                        </div>
                    </div>
                </form>

                <div class="space-y-4">
                    <div v-for="area in areas" :key="area.id" class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <div class="flex items-center justify-between gap-3 px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-bold uppercase text-white" :style="{ backgroundColor: area.color || '#171717' }">
                                    {{ area.nombre.slice(0, 2) }}
                                </span>
                                <div>
                                    <p class="font-semibold text-slate-800">{{ area.nombre }}</p>
                                    <p class="text-xs text-slate-400">{{ area.categorias.length }} categorías</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="area.activa ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500'">
                                    {{ area.activa ? 'Activa' : 'Oculta' }}
                                </span>
                                <button
                                    type="button"
                                    class="inline-flex h-8 items-center rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-600 hover:border-rojo-500 hover:text-rojo-700"
                                    @click="areaEditandoId === area.id ? (areaEditandoId = null) : abrirArea(area)"
                                >
                                    Editar
                                </button>
                                <button type="button" class="inline-flex h-8 items-center rounded-lg border border-rose-200 px-3 text-xs font-semibold text-rose-600 hover:bg-rose-50" @click="eliminarArea(area)">
                                    Eliminar
                                </button>
                            </div>
                        </div>

                        <form v-if="areaEditandoId === area.id" class="border-t border-slate-200 bg-slate-50 px-5 py-4" @submit.prevent="actualizarArea">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="etiqueta">Nombre *</label>
                                    <input v-model="areaEditando.nombre" type="text" class="campo">
                                    <InputError :mensaje="areaEditando.errors.nombre" />
                                </div>
                                <div>
                                    <label class="etiqueta">Icono</label>
                                    <select v-model="areaEditando.icono" class="campo">
                                        <option v-for="i in ICONOS" :key="i" :value="i">{{ i }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="etiqueta">Color</label>
                                    <input v-model="areaEditando.color" type="text" class="campo" placeholder="#d9232d">
                                </div>
                                <div>
                                    <label class="etiqueta">Descripción</label>
                                    <input v-model="areaEditando.descripcion" type="text" class="campo">
                                </div>
                                <label class="flex items-center gap-2 text-sm text-slate-700 sm:col-span-2">
                                    <input v-model="areaEditando.activa" type="checkbox" class="rounded border-slate-300 text-rojo-600 focus:ring-rojo-500">
                                    Área visible en la tienda
                                </label>
                            </div>
                            <div class="mt-4 flex justify-end gap-2">
                                <button type="button" class="boton-secundario" @click="areaEditandoId = null">Cancelar</button>
                                <button type="submit" class="boton-primario" :disabled="areaEditando.processing">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ===================== CATEGORÍAS ===================== -->
            <div v-else class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-600">Categorías</h3>
                            <p class="text-xs text-slate-400">Cada categoría define los campos que se piden al cargar sus productos.</p>
                        </div>
                        <button type="button" class="boton-primario" @click="prepararFormNueva">+ Crear categoría</button>
                    </div>
                </div>

                <form v-if="mostrandoNueva" class="space-y-5 rounded-2xl border-2 border-dashed border-rojo-300 bg-white p-6" @submit.prevent="guardarCategoria">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-rojo-700">Nueva categoría</h3>
                        <button type="button" class="text-sm font-semibold text-slate-400 hover:text-slate-600" @click="mostrandoNueva = false">Cancelar</button>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="etiqueta">Área *</label>
                            <select v-model="categoriaForm.area_id" class="campo">
                                <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
                            </select>
                            <InputError :mensaje="categoriaForm.errors.area_id" />
                        </div>
                        <div>
                            <label class="etiqueta">Nombre *</label>
                            <input v-model="categoriaForm.nombre" type="text" class="campo" placeholder="Ej. Cinta aislante">
                            <InputError :mensaje="categoriaForm.errors.nombre" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="etiqueta">Descripción</label>
                            <input v-model="categoriaForm.descripcion" type="text" class="campo">
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <h4 class="text-sm font-bold text-slate-600">Campos personalizados</h4>
                            <button type="button" class="text-sm font-semibold text-rojo-600 hover:text-rojo-700" @click="agregarCampo">
                                + Agregar campo
                            </button>
                        </div>

                        <p class="mb-3 text-xs text-slate-400">Definen los atributos que se piden al cargar cada producto de esta categoría.</p>

                        <div v-for="(campo, indice) in categoriaForm.campos" :key="indice" class="mb-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="grid gap-3 lg:grid-cols-6">
                                <div class="lg:col-span-2">
                                    <label class="etiqueta">Nombre *</label>
                                    <input v-model="campo.nombre" type="text" class="campo" placeholder="Ej. Diámetro">
                                </div>
                                <div class="lg:col-span-2">
                                    <label class="etiqueta">Etiqueta</label>
                                    <input v-model="campo.etiqueta" type="text" class="campo" placeholder="Ej. Diámetro">
                                </div>
                                <div>
                                    <label class="etiqueta">Tipo</label>
                                    <select v-model="campo.tipo" class="campo">
                                        <option value="texto">Texto</option>
                                        <option value="numero">Número</option>
                                        <option value="seleccion">Selección</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="etiqueta">Unidad</label>
                                    <input v-model="campo.unidad" type="text" class="campo" placeholder="mm, m, V…">
                                </div>
                                <div class="flex items-end pb-1">
                                    <label class="flex items-center gap-2 text-sm text-slate-700">
                                        <input v-model="campo.obligatorio" type="checkbox" class="rounded border-slate-300 text-rojo-600 focus:ring-rojo-500">
                                        Obligatorio
                                    </label>
                                </div>
                                <div class="flex items-end justify-end pb-1">
                                    <button type="button" class="text-sm font-semibold text-rose-600 hover:text-rose-700" @click="quitarCampo(indice)">Quitar</button>
                                </div>
                                <div v-if="campo.tipo === 'seleccion'" class="lg:col-span-6">
                                    <label class="etiqueta">Opciones (una por línea)</label>
                                    <textarea v-model="campo.opciones" rows="2" class="campo" placeholder="Rojo&#10;Negro&#10;Azul" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" class="boton-secundario" @click="prepararFormNueva">Limpiar</button>
                        <button type="submit" class="boton-primario" :disabled="categoriaForm.processing">Guardar categoría</button>
                    </div>
                    <InputError :mensaje="categoriaForm.errors['campos.0.nombre']" />
                </form>

                <div class="space-y-3">
                    <div v-for="area in areas" :key="'cat-' + area.id" class="space-y-3">
                        <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-500">
                            <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: area.color || '#171717' }" />
                            {{ area.nombre }}
                        </h3>

                        <div v-for="categoria in area.categorias" :key="categoria.id" class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            <div class="flex items-center justify-between gap-3 px-5 py-3">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ categoria.nombre }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ categoria.cantidad_productos }} productos · {{ categoria.campos.length }} campos
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="categoria.activa ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500'">
                                        {{ categoria.activa ? 'Activa' : 'Oculta' }}
                                    </span>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 items-center rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-600 hover:border-rojo-500 hover:text-rojo-700"
                                        @click="categoriaEditandoId === categoria.id ? (categoriaEditandoId = null) : abrirCategoria(categoria, area.id)"
                                    >
                                        Editar
                                    </button>
                                    <button type="button" class="inline-flex h-8 items-center rounded-lg border border-rose-200 px-3 text-xs font-semibold text-rose-600 hover:bg-rose-50" @click="eliminarCategoria(categoria)">
                                        Eliminar
                                    </button>
                                </div>
                            </div>

                            <form v-if="categoriaEditandoId === categoria.id" class="space-y-4 border-t border-slate-200 bg-slate-50 px-5 py-4" @submit.prevent="guardarCategoria">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="etiqueta">Nombre *</label>
                                        <input v-model="categoriaForm.nombre" type="text" class="campo">
                                        <InputError :mensaje="categoriaForm.errors.nombre" />
                                    </div>
                                    <div>
                                        <label class="etiqueta">Área</label>
                                        <select v-model="categoriaForm.area_id" class="campo">
                                            <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="etiqueta">Descripción</label>
                                        <input v-model="categoriaForm.descripcion" type="text" class="campo">
                                    </div>
                                    <label class="flex items-center gap-2 text-sm text-slate-700 sm:col-span-2">
                                        <input v-model="categoriaForm.activa" type="checkbox" class="rounded border-slate-300 text-rojo-600 focus:ring-rojo-500">
                                        Categoría visible en la tienda
                                    </label>
                                </div>

                                <div>
                                    <div class="mb-2 flex items-center justify-between">
                                        <h4 class="text-sm font-bold text-slate-600">Campos personalizados</h4>
                                        <button type="button" class="text-sm font-semibold text-rojo-600 hover:text-rojo-700" @click="agregarCampo">+ Agregar campo</button>
                                    </div>

                                    <div v-for="(campo, indice) in categoriaForm.campos" :key="indice" class="mb-3 rounded-xl border border-slate-200 bg-white p-4">
                                        <div class="grid gap-3 lg:grid-cols-6">
                                            <div class="lg:col-span-2">
                                                <label class="etiqueta">Nombre *</label>
                                                <input v-model="campo.nombre" type="text" class="campo">
                                            </div>
                                            <div class="lg:col-span-2">
                                                <label class="etiqueta">Etiqueta</label>
                                                <input v-model="campo.etiqueta" type="text" class="campo">
                                            </div>
                                            <div>
                                                <label class="etiqueta">Tipo</label>
                                                <select v-model="campo.tipo" class="campo">
                                                    <option value="texto">Texto</option>
                                                    <option value="numero">Número</option>
                                                    <option value="seleccion">Selección</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="etiqueta">Unidad</label>
                                                <input v-model="campo.unidad" type="text" class="campo">
                                            </div>
                                            <div class="flex items-end pb-1">
                                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                                    <input v-model="campo.obligatorio" type="checkbox" class="rounded border-slate-300 text-rojo-600 focus:ring-rojo-500">
                                                    Obligatorio
                                                </label>
                                            </div>
                                            <div class="flex items-end justify-end pb-1">
                                                <button type="button" class="text-sm font-semibold text-rose-600 hover:text-rose-700" @click="quitarCampo(indice)">Quitar</button>
                                            </div>
                                            <div v-if="campo.tipo === 'seleccion'" class="lg:col-span-6">
                                                <label class="etiqueta">Opciones (una por línea)</label>
                                                <textarea v-model="campo.opciones" rows="2" class="campo" placeholder="Rojo&#10;Negro&#10;Azul" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-2">
                                    <button type="button" class="boton-secundario" @click="categoriaEditandoId = null">Cancelar</button>
                                    <button type="submit" class="boton-primario" :disabled="categoriaForm.processing">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>