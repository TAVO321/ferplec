<script setup>
defineProps({
    mensaje: { type: String, default: '' },
});
</script>

<template>
    <p v-if="mensaje" class="mt-1 text-sm text-red-600">{{ mensaje }}</p>
</template>