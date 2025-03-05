import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel([
            'resources/ts/app.tsx',
            'resources/css/filament.scss', // Added the SCSS file
        ]),
    ],
    resolve: {
        alias: {
            '@': '/resources/ts',
        },
    },
});
