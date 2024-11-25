import forms from "@tailwindcss/forms";
import headlessui from "@headlessui/tailwindcss";
import defaultTheme from "tailwindcss/defaultTheme";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.jsx",
    ],

    theme: {
        // Définition personnalisée des tailles de police avec leur line-height
        fontSize: {
            xs: ['0.75rem', { lineHeight: '1rem' }],
            sm: ['0.875rem', { lineHeight: '1.5rem' }],
            base: ['1rem', { lineHeight: '1.75rem' }],
            lg: ['1.125rem', { lineHeight: '2rem' }],
            xl: ['1.25rem', { lineHeight: '2rem' }],
            '2xl': ['1.5rem', { lineHeight: '2rem' }],
            '3xl': ['2rem', { lineHeight: '2.5rem' }],
            '4xl': ['2.5rem', { lineHeight: '3.5rem' }],
            '5xl': ['3rem', { lineHeight: '3.5rem' }],
            '6xl': ['3.75rem', { lineHeight: '1' }],
            '7xl': ['4.5rem', { lineHeight: '1.1' }],
            '8xl': ['6rem', { lineHeight: '1' }],
            '9xl': ['8rem', { lineHeight: '1' }],
        },
        extend: {
            borderRadius: {
                '4xl': '2rem',
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                inter: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Lexend', ...defaultTheme.fontFamily.sans],
            },
            maxWidth: {
                '2xl': '40rem',
            },
        },
    },

    plugins: [forms, headlessui],
};