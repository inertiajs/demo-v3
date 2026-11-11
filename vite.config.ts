import inertia from '@inertiajs/vite';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig, lazyPlugins } from 'vite-plus';

export default defineConfig(({ command }) => ({
    // Expose the project root to the client so SourceLinks can open files in VSCode during development
    define:
        command === 'serve'
            ? { __PROJECT_ROOT__: JSON.stringify(process.cwd()) }
            : {},
    build: {
        minify: false,
    },
    plugins: lazyPlugins(() => [
        laravel({
            input: ['resources/js/app.ts'],
            refresh: true,
        }),
        inertia(),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ]),
    server: {
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/vendor/**',
            ],
        },
    },
    lint: {
        plugins: ['vue', 'typescript', 'import'],
        env: {
            browser: true,
            node: true,
        },
        ignorePatterns: [
            'vendor/**',
            'node_modules/**',
            'public/**',
            'bootstrap/ssr/**',
            'resources/js/components/ui/*',
            'resources/js/wayfinder/**',
        ],
        rules: {
            'typescript/no-explicit-any': 'off',
            'typescript/consistent-type-imports': [
                'error',
                {
                    prefer: 'type-imports',
                    fixStyle: 'separate-type-imports',
                },
            ],
        },
        options: {
            denyWarnings: true,
            typeAware: true,
        },
    },
    fmt: {
        printWidth: 80,
        tabWidth: 4,
        singleQuote: true,
        semi: true,
        singleAttributePerLine: false,
        htmlWhitespaceSensitivity: 'css',
        sortPackageJson: false,
        ignorePatterns: [
            '.agents/**',
            '.claude/**',
            '.cursor/**',
            '.github/**',
            '.junie/**',
            'AGENTS.md',
            'CLAUDE.md',
            'composer.json',
            'resources/js/components/ui/*',
            'resources/js/wayfinder/**',
            'resources/views/mail/*',
        ],
        overrides: [
            {
                files: ['**/*.yml'],
                options: { tabWidth: 2 },
            },
        ],
        sortTailwindcss: {
            functions: ['clsx', 'cn', 'cva'],
            stylesheet: 'resources/css/app.css',
        },
    },
}));
