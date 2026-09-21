<script setup>
import { computed } from 'vue';

const props = defineProps({
    nombre: { type: String, default: 'tienda' },
    clase: { type: String, default: 'h-6 w-6' },
});

const PATHS = {
    herramientas: [
        'M14.7 6.3c0 2.1-1.7 3.8-3.8 3.8S7.1 8.4 7.1 6.3 8.8 2.5 10.9 2.5s3.8 1.7 3.8 3.8z',
        'M10.9 10.1v4.6',
        'M7.2 10.6 2.8 14.9a2 2 0 0 0 2.8 2.8l4.3-4.3',
        'M21.2 14.6 16.9 10.3l-1.9 1.9 4.3 4.3a2 2 0 0 0 2.8-2.8z',
    ],
    llave_agua: [
        'M14.5 4.5a3 3 0 0 0-4 4L3 16a2.3 2.3 0 0 0 3.3 3.3l7.5-7.5a3 3 0 0 0 4-4l-2.8 2.8-3-3 2.5-2.1z',
        'M15.8 12.8l2.6 2.6a2.6 2.6 0 1 1-3.7 3.7l-2.5-2.6',
    ],
    rayo: ['M13 2 4.5 13.5H11L9 22l8.5-11.5H11L13 2z'],
    tienda: [
        'M3 10h18M5.5 10V20h13V10M5.5 10l2-6h9l2 6M9.5 20v-5a1.5 1.5 0 0 1 3 0v5',
    ],
    camion: [
        'M2 7h11v9H2zM13 10h4l4 3v3h-8z',
        'M7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM17 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4z',
    ],
    escudo: [
        'M12 3 5 6v6c0 4 3.1 6.7 7 8 3.9-1.3 7-4 7-8V6l-7-3z',
        'M9.5 12.2 11.4 14l3.6-3.6',
    ],
};

const paths = computed(() => PATHS[props.nombre] ?? PATHS.tienda);
</script>

<template>
    <svg
        :class="clase"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <path v-for="(d, i) in paths" :key="i" :d="d" />
    </svg>
</template>