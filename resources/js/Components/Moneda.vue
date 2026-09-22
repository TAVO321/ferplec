<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    valor: { type: [Number, String], required: true },
    simbolo: { type: String, default: '' },
    clase: { type: String, default: '' },
});

const monedaTienda = computed(() => usePage().props.tienda?.moneda ?? 'Bs');
const simbolo = computed(() => props.simbolo || monedaTienda.value);

const texto = computed(() => {
    const numero = Number(props.valor);
    if (Number.isNaN(numero)) {
        return `${simbolo.value} 0,00`;
    }

    return `${simbolo.value} ${new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numero)}`;
});
</script>

<template>
    <span :class="clase">{{ texto }}</span>
</template>