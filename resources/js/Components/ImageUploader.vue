<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    url: { type: String, required: true },
});

const emit = defineEmits(['update:modelValue']);

const input = ref(null);
const cargando = ref(false);
const error = ref('');

async function subir(evento) {
    const archivo = evento.target.files?.[0];
    if (!archivo) {
        return;
    }

    cargando.value = true;
    error.value = '';

    try {
        const fd = new FormData();
        fd.append('imagen', archivo);
        const { data } = await axios.post(props.url, fd);
        emit('update:modelValue', [...props.modelValue, data]);
    } catch (err) {
        error.value = err.response?.data?.message ?? 'No se pudo subir la imagen.';
    } finally {
        cargando.value = false;
        if (input.value) {
            input.value.value = '';
        }
    }
}

function quitar(indice) {
    emit('update:modelValue', props.modelValue.filter((_, i) => i !== indice));
}
</script>

<template>
    <div>
        <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
            <div
                v-for="(archivo, i) in modelValue"
                :key="archivo.ruta"
                class="group relative aspect-square overflow-hidden rounded-xl border border-slate-200 bg-white"
            >
                <img
                    v-if="archivo.url"
                    :src="archivo.url"
                    :alt="`Imagen ${i + 1}`"
                    class="h-full w-full object-cover"
                >
                <div v-else class="flex h-full w-full items-center justify-center text-xs text-slate-300">Imagen</div>

                <button
                    type="button"
                    class="absolute right-1.5 top-1.5 flex h-7 w-7 items-center justify-center rounded-lg bg-black/70 text-white opacity-0 transition group-hover:opacity-100"
                    :aria-label="`Quitar imagen ${i + 1}`"
                    @click="quitar(i)"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M6 6l12 12M6 18L18 6" stroke-linecap="round" /></svg>
                </button>
            </div>

            <button
                type="button"
                class="flex aspect-square flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-slate-300 text-slate-400 transition hover:border-rojo-500 hover:text-rojo-600"
                :disabled="cargando"
                @click="input?.click()"
            >
                <span v-if="cargando" class="h-5 w-5 animate-spin rounded-full border-2 border-slate-300 border-t-rojo-600" />
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6"><path d="M12 4v12m-6-6l6-6 6 6M5 20h14" stroke-linecap="round" stroke-linejoin="round" /></svg>
                <span class="px-2 text-center text-[11px] font-medium">{{ cargando ? 'Subiendo…' : 'Agregar imagen' }}</span>
            </button>
        </div>

        <input
            ref="input"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            class="hidden"
            @change="subir"
        >

        <p v-if="error" class="mt-2 text-sm text-rose-600">{{ error }}</p>
        <p class="mt-2 text-xs text-slate-400">JPG, PNG o WebP · máximo 5 MB. La primera imagen es la principal.</p>
    </div>
</template>