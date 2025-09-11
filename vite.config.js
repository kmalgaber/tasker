import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        https: {
            key: fs.readFileSync((process.env.TLS_DIR || './storage/app/certs') + '/server.key'),
            cert: fs.readFileSync((process.env.TLS_DIR || './storage/app/certs') + '/server.crt'),
        },
        hmr: {
            host: 'tasker.local',
        }
    },
});
