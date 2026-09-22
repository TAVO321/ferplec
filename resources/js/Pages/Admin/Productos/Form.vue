<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import InputError from '../../../Components/InputError.vue';
import ImageUploader from '../../../Components/ImageUploader.vue';

const props = defineProps({
    producto: { type: Object, default: null },
    categorias: { type: Array, required: true },
});

const agruparAreas = computed(() => {
    const mapa = new Map();

    for (const categoria of props.categorias) {
        const clave = categoria.area || 'Sin área';
        if (!mapa.has(clave)) {
            mapa.set(clave, []);
        }
        mapa.get(clave).push(categoria);
    }

    return Array.from(mapa, ([nombre, categorias]) => ({ nombre, categorias }));
});

const DISPONIBILIDADES = {
    disponible: 'Disponible',
    bajo_pedido: 'Bajo pedido',
    agotado: 'Agotado',
};

const form = useForm({
    categoria_id: props.producto?.categoria_id ?? null,
    nombre: props.producto?.nombre ?? '',
    descripcion: props.producto?.descripcion ?? '',
    codigo: props.producto?.codigo ?? '',
    marca: props.producto?.marca ?? '',
    unidad_de_medida: props.producto?.unidad_de_medida ?? 'und',
    disponibilidad: props.producto?.disponibilidad ?? 'disponible',
    precio: props.producto?.precio ?? '',
    precio_oferta: props.producto?.precio_oferta ?? '',
    stock: props.producto?.stock ?? 0,
    activo: props.producto?.activo ?? true,
    destacado: props.producto?.destacado ?? false,
    valores: { ...(props.producto?.valores ?? {}) },
    imagenes: (props.producto?.imagenes ?? []).map((i) => ({ ruta: i.ruta, url: i.url })),
});

const categoriaActual = computed(() => props.categorias.find((c) => c.id === form.categoria_id) ?? null);

watch(
    () => form.categoria_id,
    (id) => {
        const categoria = props.categorias.find((c) => c.id === id);
        if (!categoria) {
            return;
        }
        const campos = { ...form.valores };
        for (const campo of categoria.campos) {
            const clave = String(campo.id);
            if (!(clave in campos)) {
                campos[clave] = '';
            }
        }
        form.valores = campos;
    },
);

function etiqueta(campo) {
    return (campo.etiqueta ?? campo.nombre) + (campo.obligatorio ? ' *' : '');
}

function guardar() {
    form.transform((datos) => ({
        ...datos,
        imagenes: (datos.imagenes ?? []).map((i) => i.ruta),
    }));

    if (props.producto) {
        form.put(route('admin.productos.update', props.producto.id));
    } else {
        form.post(route('admin.productos.store'));
    }
}
</script>

<template>
    <AdminLayout>
        <form class="mx-auto max-w-4xl space-y-6" @submit.prevent="guardar">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ producto ? 'Editar producto' : 'Nuevo producto' }}</h2>
                    <p class="text-sm text-slate-500">Completá los datos y los atributos de la categoría.</p>
                </div>
                <div class="flex gap-2">
                    <a href="/admin/productos" class="boton-secundario">Cancelar</a>
                    <button type="submit" class="boton-primario" :disabled="form.processing">
                        {{ form.processing ? 'Guardando…' : 'Guardar producto' }}
                    </button>
                </div>
            </div>

            <div v-if="form.errors && Object.keys(form.errors).length" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-600">
                Revisá los campos marcados en rojo.
            </div>

            <div class="grid gap-6 rounded-2xl border border-slate-200 bg-white p-6 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label for="categoria_id" class="etiqueta">Categoría *</label>
                    <select id="categoria_id" v-model="form.categoria_id" class="campo" required>
                        <option :value="null" disabled>Seleccioná una categoría</option>
                        <optgroup v-for="area in agruparAreas" :key="area.nombre" :label="area.nombre">
                            <option v-for="c in area.categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                        </optgroup>
                    </select>
                    <InputError :mensaje="form.errors.categoria_id" />
                </div>

                <div>
                    <label for="nombre" class="etiqueta">Nombre *</label>
                    <input id="nombre" v-model="form.nombre" type="text" class="campo" placeholder="Ej. Cinta aislante 18m">
                    <InputError :mensaje="form.errors.nombre" />
                </div>

                <div>
                    <label for="codigo" class="etiqueta">Codigo</label>
                    <input id="codigo" v-model="form.codigo" type="text" class="campo" placeholder="Ej. CA-180">
                    <InputError :mensaje="form.errors.codigo" />
                </div>

                <div>
                    <label for="marca" class="etiqueta">Marca</label>
                    <input id="marca" v-model="form.marca" type="text" class="campo" placeholder="Ej. Truper">
                    <InputError :mensaje="form.errors.marca" />
                </div>

                <div>
                    <label for="unidad_de_medida" class="etiqueta">Unidad de medida</label>
                    <input id="unidad_de_medida" v-model="form.unidad_de_medida" type="text" class="campo" placeholder="und, metro, kg, caja...">
                    <InputError :mensaje="form.errors.unidad_de_medida" />
                </div>

                <div>
                    <label for="disponibilidad" class="etiqueta">Disponibilidad</label>
                    <select id="disponibilidad" v-model="form.disponibilidad" class="campo">
                        <option v-for="(etiqueta, clave) in DISPONIBILIDADES" :key="clave" :value="clave">{{ etiqueta }}</option>
                    </select>
                    <InputError :mensaje="form.errors.disponibilidad" />
                </div>

                <div class="lg:col-span-2">
                    <label for="descripcion" class="etiqueta">Descripción</label>
                    <textarea id="descripcion" v-model="form.descripcion" rows="3" class="campo" placeholder="Descripción breve para el cliente…" />
                    <InputError :mensaje="form.errors.descripcion" />
                </div>

                <div>
                    <label for="precio" class="etiqueta">Precio (Bs) *</label>
                    <input id="precio" v-model="form.precio" type="number" min="0" step="0.01" class="campo" placeholder="0.00">
                    <InputError :mensaje="form.errors.precio" />
                </div>

                <div>
                    <label for="precio_oferta" class="etiqueta">Precio de oferta (Bs)</label>
                    <input id="precio_oferta" v-model="form.precio_oferta" type="number" min="0" step="0.01" class="campo" placeholder="Opcional">
                    <InputError :mensaje="form.errors.precio_oferta" />
                </div>

                <div>
                    <label for="stock" class="etiqueta">Stock *</label>
                    <input id="stock" v-model="form.stock" type="number" min="0" step="1" class="campo">
                    <InputError :mensaje="form.errors.stock" />
                </div>

                <div class="flex items-end gap-6 pb-1">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input v-model="form.activo" type="checkbox" class="rounded border-slate-300 text-rojo-600 focus:ring-rojo-500">
                        Visible en la tienda
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input v-model="form.destacado" type="checkbox" class="rounded border-slate-300 text-rojo-600 focus:ring-rojo-500">
                        Destacado en portada
                    </label>
                </div>
            </div>

            <div v-if="categoriaActual" class="rounded-2xl border border-slate-200 bg-white p-6">
                <div class="mb-4 border-b border-slate-200 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-600">Atributos de {{ categoriaActual.nombre }}</h3>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div v-for="campo in categoriaActual.campos" :key="campo.id">
                        <label :for="'campo_' + campo.id" class="etiqueta">{{ etiqueta(campo) }}</label>

                        <select
                            v-if="campo.tipo === 'seleccion'"
                            :id="'campo_' + campo.id"
                            v-model="form.valores[campo.id]"
                            class="campo"
                        >
                            <option value="">—</option>
                            <option v-for="opcion in campo.opciones" :key="opcion" :value="opcion">{{ opcion }}</option>
                        </select>

                        <input
                            v-else
                            :id="'campo_' + campo.id"
                            v-model="form.valores[campo.id]"
                            type="text"
                            class="campo"
                            :placeholder="campo.unidad ? `Ej. 2.5 ${campo.unidad}` : 'Ingresá un valor'"
                        >
                        <InputError :mensaje="form.errors['valores.' + campo.id]" />
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-600">Imágenes</h3>
                <ImageUploader
                    v-model="form.imagenes"
                    :url="route('admin.productos.imagen')"
                />
            </div>

            <div class="flex justify-end gap-2 pb-8">
                <a href="/admin/productos" class="boton-secundario">Cancelar</a>
                <button type="submit" class="boton-primario" :disabled="form.processing">
                    {{ form.processing ? 'Guardando…' : 'Guardar producto' }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>