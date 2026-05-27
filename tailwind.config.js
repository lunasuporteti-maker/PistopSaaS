import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    // Permite usar data-theme="dark" no HTML raiz
    darkMode: ['attribute', '[data-theme="dark"]'],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Geist', 'Inter', ...defaultTheme.fontFamily.sans],
                mono: ['Geist Mono', 'JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                brand: {
                    50:  'oklch(97% 0.02 60)',
                    100: 'oklch(94% 0.05 55)',
                    200: 'oklch(88% 0.10 55)',
                    300: 'oklch(80% 0.14 52)',
                    400: 'oklch(74% 0.17 50)',
                    500: 'oklch(68% 0.18 48)',
                    600: 'oklch(62% 0.19 45)',
                    700: 'oklch(54% 0.18 42)',
                    800: 'oklch(44% 0.15 40)',
                    900: 'oklch(34% 0.12 38)',
                },
            },
            borderRadius: {
                xs:   '4px',
                sm:   '6px',
                md:   '8px',
                lg:   '12px',
                xl:   '16px',
                pill: '999px',
            },
            boxShadow: {
                xs: '0 1px 2px rgba(15, 18, 28, 0.04)',
                sm: '0 1px 2px rgba(15, 18, 28, 0.05), 0 1px 3px rgba(15, 18, 28, 0.04)',
                md: '0 2px 4px rgba(15, 18, 28, 0.04), 0 4px 12px rgba(15, 18, 28, 0.06)',
                lg: '0 8px 24px rgba(15, 18, 28, 0.08), 0 2px 6px rgba(15, 18, 28, 0.05)',
                xl: '0 20px 40px rgba(15, 18, 28, 0.12), 0 4px 12px rgba(15, 18, 28, 0.06)',
            },
        },
    },

    plugins: [forms],
};
