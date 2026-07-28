import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

function scale(hex) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    const m = (r1, g1, b1, r2, g2, b2, t) =>
        `rgb(${Math.round(r1 + (r2 - r1) * t)} ${Math.round(g1 + (g2 - g1) * t)} ${Math.round(b1 + (b2 - b1) * t)})`;
    return {
        50: m(r, g, b, 255, 255, 255, 0.92),
        100: m(r, g, b, 255, 255, 255, 0.82),
        200: m(r, g, b, 255, 255, 255, 0.65),
        300: m(r, g, b, 255, 255, 255, 0.45),
        400: m(r, g, b, 255, 255, 255, 0.22),
        500: hex,
        600: m(r, g, b, 0, 0, 0, 0.15),
        700: m(r, g, b, 0, 0, 0, 0.30),
        800: m(r, g, b, 0, 0, 0, 0.50),
        900: m(r, g, b, 0, 0, 0, 0.70),
        950: m(r, g, b, 0, 0, 0, 0.85),
    };
}

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['DM Sans', ...defaultTheme.fontFamily.sans],
                display: ['Poppins', 'sans-serif'],
                label: ['DM Sans', ...defaultTheme.fontFamily.sans],
                mono: ['ui-monospace', 'SFMono-Regular', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                ink: {
                    50: '#fafafa',
                    100: '#f4f4f5',
                    200: '#e4e4e7',
                    300: '#d4d4d8',
                    400: '#a1a1aa',
                    500: '#71717a',
                    600: '#52525b',
                    700: '#3f3f3f',
                    800: '#27272a',
                    850: '#1c1c1f',
                    900: '#18181b',
                    950: '#09090b',
                },
                primary: scale('#5D87FF'),
                secondary: scale('#49BEFF'),
                warning: scale('#FFAE1F'),
                success: scale('#13DEB9'),
                info: scale('#539BFF'),
                ember: scale('#FA896B'),
                accent: scale('#5D87FF'),
                violet: scale('#5D87FF'),
                green: scale('#13DEB9'),
                amber: scale('#FFAE1F'),
                blue: scale('#5D87FF'),
                indigo: scale('#615DFF'),
                pink: scale('#EC4899'),
            },
        },
    },
    plugins: [forms],
};
