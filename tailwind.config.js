/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                rojo: {
                    50: '#FEF2F3',
                    100: '#FDE3E6',
                    200: '#FBCBD0',
                    300: '#F8A5AE',
                    400: '#F2707F',
                    500: '#E74354',
                    600: '#D9232D',
                    700: '#B81620',
                    800: '#98161F',
                    900: '#7E1820',
                },
                marfil: '#FBF6EE',
                carbon: '#171717',
            },
            fontFamily: {
                sans: ['Figtree', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                display: ['Fraunces', 'serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};