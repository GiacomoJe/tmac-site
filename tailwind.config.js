import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
        './app/Filament/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                // Tipografia industrial — alinhada ao protótipo TMAC
                display: ['Archivo', ...defaultTheme.fontFamily.sans],
                sans: ['"IBM Plex Sans"', ...defaultTheme.fontFamily.sans],
                mono: ['"IBM Plex Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                // Paleta TMAC — brutalist industrial
                bg: {
                    DEFAULT: '#F5F4F0',     // off-white quente (fundo)
                    elev: '#FFFFFF',         // cards elevados
                    sunken: '#EDECE7',       // baixados (search, chips)
                },
                ink: {
                    DEFAULT: '#1A1B1F',      // texto principal
                    soft: '#52535A',         // texto secundário
                    faint: '#8A8B91',        // texto faint, eyebrows
                },
                line: {
                    DEFAULT: '#D9D8D2',      // bordas 1px
                    soft: '#E8E7E2',         // bordas internas
                },
                accent: {
                    DEFAULT: '#ED1C24',      // vermelho TMAC oficial (Pantone 2347 C)
                    ink: '#FFFFFF',
                    soft: '#FCEBEC',
                    dark: '#8B0304',         // vermelho escuro (Pantone 1815 C)
                },
                signal: {
                    DEFAULT: '#004F9F',      // azul TMAC oficial (Pantone 2145 C)
                    ink: '#FFFFFF',
                    soft: '#E6EDFF',
                    dark: '#003063',         // azul intermediário (Pantone 2147 C)
                    darker: '#001437',       // azul mais escuro (Pantone 2768 C)
                },
                whatsapp: '#25D366',
                warn: '#E89A2B',
            },
            borderRadius: {
                sm: '6px',
                DEFAULT: '10px',
                lg: '14px',
            },
            letterSpacing: {
                tightest: '-0.03em',
                tighter: '-0.02em',
                tightish: '-0.015em',
                'mono-up': '0.08em',
            },
            container: {
                center: true,
                padding: {
                    DEFAULT: '1rem',
                    sm: '1.25rem',
                    lg: '2rem',
                },
                screens: {
                    '2xl': '1280px',
                },
            },
        },
    },
    plugins: [forms, typography],
};
