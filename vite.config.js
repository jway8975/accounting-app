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
    server: {
        host: 'accounting-app.test', // 绑定到你的虚拟域名
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'accounting-app.test', // 确保 HMR 也使用这个域名
        },
    }
});
