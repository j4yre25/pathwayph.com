import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({

    plugins: [
        laravel({
            input: 'resources/js/app.js',
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
        rollupOptions: {
            output: {
                manualChunks: {
                    // Split large dependencies into separate chunks
                    vue: ['vue'],
                    apexcharts: ['vue3-apexcharts'],
                    dateFns: ['date-fns'],
                },
            },
        },
        chunkSizeWarningLimit: 1000, // Adjust the chunk size warning limit (default is 500 kB)
    },

    
});
