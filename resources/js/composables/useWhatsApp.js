import { ref, computed, onMounted } from 'vue';

const esMovil = ref(false);

function detectar() {
    if (typeof navigator === 'undefined') {
        return false;
    }

    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

export function useWhatsApp() {
    onMounted(() => {
        esMovil.value = detectar();
    });

    const urlWhatsApp = computed(() => (numero, mensaje = '') => {
        const limpio = String(numero ?? '').replace(/\D/g, '');
        if (!limpio) {
            return '#';
        }

        const texto = encodeURIComponent(mensaje);
        const base = esMovil.value
            ? 'https://api.whatsapp.com/send'
            : 'https://web.whatsapp.com/send';

        return `${base}?phone=${limpio}${texto ? `&text=${texto}` : ''}`;
    });

    return {
        esMovil,
        urlWhatsApp,
    };
}
