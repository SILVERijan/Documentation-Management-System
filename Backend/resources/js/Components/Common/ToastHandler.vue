<script setup lang="ts">
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast, type ToastOptions } from 'vue3-toastify';

const page = usePage();

const STORAGE_KEY = 'shown_flash_ids';

/**
 * Read the set of already-displayed flash IDs from sessionStorage.
 * sessionStorage survives Inertia SPA navigation but is cleared when the
 * tab is closed — exactly the lifetime we want.
 */
function getShownIds(): Set<string> {
    try {
        const raw = sessionStorage.getItem(STORAGE_KEY);
        return raw ? new Set<string>(JSON.parse(raw)) : new Set<string>();
    } catch {
        return new Set<string>();
    }
}

function markShown(id: string): void {
    try {
        const ids = getShownIds();
        ids.add(id);
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify([...ids]));
    } catch {
        // sessionStorage unavailable — silently ignore
    }
}

const notify = () => {
    const flash = page.props.flash as { id?: string; message?: string; type?: string } | null;

    if (!flash?.id || !flash?.message) {
        return;
    }

    // Skip if this exact flash message was already shown during this browser session
    if (getShownIds().has(flash.id)) {
        return;
    }

    markShown(flash.id);

    const isError = flash.type === 'error';
    const toastMethod = isError ? toast.error : toast.success;

    toastMethod(flash.message, {
        transition: toast.TRANSITIONS.SLIDE,
        toastStyle: {
            backgroundColor: '#16161a',
            border: '1px solid #262626',
            color: isError ? '#ef4444' : '#10b981',
        },
        progressStyle: {
            background: isError ? '#ef4444' : '#10b981',
        },
    } as ToastOptions);
};

// Watch for ANY change to the flash prop (fires on every Inertia page visit / response)
watch(() => page.props.flash, () => {
    notify();
}, { deep: true, immediate: true });
</script>

<template>
    <!-- This component does not render any visible HTML -->
</template>
