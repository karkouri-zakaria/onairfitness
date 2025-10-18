/** @type {import('tailwindcss').Config} */
export const content = [
  './src/**/*.{html,js}',
  './index.php'
];
export const theme = {
  extend: {
    colors: {
      'bricks': '#87221d',
      'guardsman': '#bf0007',
      'espresso': '#5c1b18',
      'torch': '#fc002f',
    },
    fontFamily: {
      houstiq: ['Houstiq', 'sans-serif'],
      gilroy: ['Gilroy', 'sans-serif'],
      saira: ['Saira', 'sans-serif'],
      orbitron: ['Orbitron', 'sans-serif'],
    },
  },
};
export const plugins = [
  require('tailwindcss'),
  require('autoprefixer'),
];