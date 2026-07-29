import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/accept-phone.js', 'resources/js/accept-otp.js', 'resources/js/voucher.js', 'resources/js/event-vouchers.js', 'resources/js/scanner.js'],
            refresh: true,
            fonts: [
                bunny('Montserrat', {
                    weights: [400, 700],
                }),
                bunny('Cairo', {
                    weights: [400, 700],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
