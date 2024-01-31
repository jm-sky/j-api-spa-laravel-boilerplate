import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors'

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './src/**/*.vue',
    './src/**/*.ts',
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
      colors: {
        primary: colors.blue,
      },
    },
  },

  plugins: [forms],
};
