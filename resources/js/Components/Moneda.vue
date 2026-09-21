<script setup>
import { computed } from 'vue';

const props = defineProps({
    valor: { type: [Number, String], required: true },
    simbolo: { type: String, default: 'Bs' },
    clase: { type: String, default: '' },
});

const texto = computed(() => {
    const numero = Number(props.valor);
    if (Number.isNaN(numero)) {
        return `Bs 0`;
    }

    return `${props.simbolo} ${new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numero)}`;
});
</script>

<template>
    <span :class="clase">{{ texto }}</span>
</template>