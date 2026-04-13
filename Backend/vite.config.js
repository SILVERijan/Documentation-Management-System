import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.ts',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        chunkSizeWarningLimit: 600,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/chart.js') || id.includes('node_modules/vue-chartjs')) {
                        return 'vendor-chart';
                    }
                    if (id.includes('@tiptap')) {
                        return 'vendor-tiptap';
                    }
                    if (id.includes('node_modules/mammoth') || id.includes('node_modules/marked')) {
                        return 'vendor-docparser';
                    }
                    if (id.includes('node_modules/vue3-toastify')) {
                        return 'vendor-toast';
                    }
                    if (id.includes('node_modules/vue') || id.includes('node_modules/@inertiajs') || id.includes('node_modules/@vue')) {
                        return 'vendor-vue';
                    }
                    if (id.includes('node_modules/lucide-vue-next')) {
                        return 'vendor-icons';
                    }
                },
            },
        },
    },
});
