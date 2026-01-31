/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./php/**/*.php",
    "./templates/**/*.php",
    "./assets/js/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        'indet-green': '#006400',
        'indet-red': '#FF0000',
      },
      fontFamily: {
        montserrat: ['Montserrat', 'sans-serif'],
        poppins: ['Poppins', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
