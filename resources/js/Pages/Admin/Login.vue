<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '../../Components/InputError.vue';

const form = useForm({
    email: '',
    password: '',
    recuerdame: false,
});

function ingresar() {
    form.post(route('admin.login.store'), {
        onSuccess: () => form.reset('password'),
    });
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-black px-4">
        <div class="pointer-events-none fixed -right-40 -top-40 h-96 w-96 rounded-full bg-rojo-700/30 blur-3xl" />

        <div class="relative w-full max-w-md">
            <div class="mb-6 flex flex-col items-center text-center">
                <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-rojo-600 text-4xl font-bold text-white">F</span>
                <h1 class="titulo-marca mt-4 text-2xl font-bold text-white">FER<span class="text-rojo-500">PLEC</span></h1>
                <p class="mt-1 text-sm text-slate-400">Acceso al panel de administración</p>
            </div>

            <form class="rounded-2xl bg-white p-6 shadow-xl" @submit.prevent="ingresar">
                <div>
                    <label class="etiqueta" for="email">Correo electrónico</label>
                    <input id="email" v-model="form.email" type="email" autocomplete="username" inputmode="email" autocapitalize="none" autocorrect="off" spellcheck="false" class="campo" placeholder="admin@ferplec.com" :disabled="form.processing">
                    <InputError :mensaje="form.errors.email" />
                </div>

                <div class="mt-4">
                    <label class="etiqueta" for="password">Contraseña</label>
                    <input id="password" v-model="form.password" type="password" autocomplete="current-password" autocapitalize="none" autocorrect="off" spellcheck="false" class="campo" placeholder="••••••••" :disabled="form.processing">
                    <InputError :mensaje="form.errors.password" />
                </div>

                <label class="mt-4 flex items-center gap-2 text-sm text-slate-600">
                    <input v-model="form.recuerdame" type="checkbox" class="rounded border-slate-300 text-rojo-600 focus:ring-rojo-500">
                    Recordarme
                </label>

                <button type="submit" class="boton-primario mt-6 w-full !py-3" :disabled="form.processing">
                    {{ form.processing ? 'Ingresando…' : 'Ingresar' }}
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                <a href="/" class="hover:text-white">← Volver a la tienda</a>
            </p>
        </div>
    </div>
</template>