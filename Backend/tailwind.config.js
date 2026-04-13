import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    safelist: [
        {
            pattern: /(bg|text|border)-(indigo|red|emerald|orange|blue|rose|amber|teal|cyan|sky|violet|fuchsia|pink|slate|gray)-(400|500|600)/,
            variants: ['hover', 'group-hover', 'group-hover/hover'],
        },
        {
            pattern: /(bg|text|border)-(indigo|red|emerald|orange|blue|rose|amber|teal|cyan|sky|violet|fuchsia|pink|slate|gray)-500\/[0-9]{1,2}/,
            variants: ['hover', 'group-hover'],
        }
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography],
};
