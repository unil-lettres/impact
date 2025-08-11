import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/sass/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
        react(),
    ],
    esbuild: {
        // Allow JSX syntax inside .js files within resources/js
        loader: 'jsx',
        include: /resources\/js\/.*\.js$/,
        exclude: [],
    },
    define: {
        // Avoid "process is not defined" console error
        'process.platform': JSON.stringify('linux'),
    },
    // Silence Sass deprecation warnings.
    // TODO: Remove when bootsrap deprecations are fixed.
    // https://github.com/twbs/bootstrap/blob/main/site/src/content/docs/getting-started/vite.mdx#configure-vite
    css: {
        preprocessorOptions: {
            scss: {
                silenceDeprecations: [
                    'import',
                    'mixed-decls',
                    'color-functions',
                    'global-builtin',
                ],
            },
        },
    },
});
