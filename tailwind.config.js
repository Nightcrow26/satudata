/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/**/*.php",
  ],
  darkMode: 'class', // Enable class-based dark mode
  theme: {
    extend: {
      fontFamily: {
        'body': ['Inter', 'sans-serif'],
        'heading': ['Roboto', 'sans-serif'],
      },
    },
  },
  plugins: [],
}