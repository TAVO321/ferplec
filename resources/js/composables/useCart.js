import { ref, computed, onMounted } from 'vue';

const CART_KEY = 'ferplec_cart';
const CART_MAX_ITEMS = 20;
const CART_TTL_HOURS = 24;

const items = ref([]);
const listo = ref(false);

function ahora() {
    return Date.now();
}

function normalizarProducto(producto) {
    return {
        producto_id: Number(producto.id || producto.producto_id),
        nombre: String(producto.nombre || ''),
        slug: String(producto.slug || ''),
        marca: String(producto.marca || ''),
        unidad_de_medida: String(producto.unidad_de_medida || 'und'),
        disponibilidad: String(producto.disponibilidad || 'disponible'),
        precio: Number(producto.precio || 0),
        precio_anterior: producto.precio_anterior ? Number(producto.precio_anterior) : null,
        imagen: String(producto.imagen || ''),
        stock: Number(producto.stock || 0),
    };
}

function cargar() {
    if (typeof window === 'undefined') {
        return;
    }

    try {
        const raw = window.localStorage.getItem(CART_KEY);
        if (!raw) {
            items.value = [];
            listo.value = true;
            return;
        }

        const datos = JSON.parse(raw);
        const expiracion = datos.expiracion ?? 0;

        if (ahora() > expiracion) {
            limpiar();
            listo.value = true;
            return;
        }

        items.value = (datos.items || [])
            .filter((item) => item.producto_id && item.cantidad > 0)
            .slice(0, CART_MAX_ITEMS);
    } catch {
        items.value = [];
    }

    listo.value = true;
}

function guardar() {
    if (typeof window === 'undefined') {
        return;
    }

    const expiracion = ahora() + CART_TTL_HOURS * 60 * 60 * 1000;
    window.localStorage.setItem(CART_KEY, JSON.stringify({
        items: items.value,
        expiracion,
    }));
}

function agregar(producto, cantidad = 1) {
    const datos = normalizarProducto(producto);
    const id = datos.producto_id;
    const cantidadFinal = Math.min(99, Math.max(1, Number(cantidad) || 1));
    const existente = items.value.find((i) => i.producto_id === id);

    if (existente) {
        existente.cantidad = Math.min(99, existente.cantidad + cantidadFinal);
    } else {
        if (items.value.length >= CART_MAX_ITEMS) {
            return { ok: false, mensaje: 'Carrito lleno (maximo 20 productos).' };
        }
        items.value.push({
            ...datos,
            cantidad: cantidadFinal,
            agregado_en: ahora(),
        });
    }

    guardar();
    return { ok: true };
}

function actualizar(productoId, cantidad) {
    const id = Number(productoId);
    const index = items.value.findIndex((i) => i.producto_id === id);

    if (index === -1) {
        return;
    }

    if (cantidad <= 0) {
        items.value.splice(index, 1);
    } else {
        items.value[index].cantidad = Math.min(99, Math.max(1, cantidad));
    }

    guardar();
}

function quitar(productoId) {
    actualizar(productoId, 0);
}

function limpiar() {
    items.value = [];
    if (typeof window !== 'undefined') {
        window.localStorage.removeItem(CART_KEY);
    }
}

export function useCart() {
    const contar = computed(() => items.value.reduce((suma, item) => suma + item.cantidad, 0));
    const total = computed(() => items.value.reduce((suma, item) => suma + (Number(item.precio) * item.cantidad), 0));

    onMounted(() => {
        if (!listo.value) {
            cargar();
        }
    });

    return {
        items,
        listo,
        contar,
        total,
        agregar,
        actualizar,
        quitar,
        limpiar,
        cargar,
    };
}
