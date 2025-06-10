/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'primary': '#3498db',
        'primary-dark': '#2980b9',
        'secondary': '#2ecc71',
        'secondary-dark': '#27ae60',
        'dark': '#2c3e50',
        'dark-light': '#34495e',
        'light': '#f8f9fa',
        'text': '#2c3e50',
        'text-light': '#7f8c8d',
        'danger': '#e74c3c',
        'success': '#2ecc71',
      },
    },
  },
  plugins: [],
}
