/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class', // Enable dark mode support via class strategy
  theme: {
    extend: {
      colors: {
        forest: {
          light: '#4A6F4A', // lighter variant for hover etc.
          DEFAULT: '#2E4A32',
          dark: '#1E2F23',
        },
        kraft: {
          light: '#E6D7B9',
          DEFAULT: '#D9C29A',
          dark: '#A88F66',
        },
        beige: {
          light: '#F8F5F2',
          DEFAULT: '#F1EDE6',
          dark: '#CDC6BD',
        },
        whiteSoft: '#FFFFFF',
        nearBlackGreen: '#1E2F23',
      },
      boxShadow: {
        'forest-light': '0 8px 16px rgba(46, 74, 50, 0.3)',
        'kraft-light': '0 4px 12px rgba(217, 194, 154, 0.3)',
        'beige-soft': '0 2px 6px rgba(241, 237, 230, 0.4)',
      },
      backgroundImage: {
        'forest-gradient': 'linear-gradient(135deg, #2E4A32 0%, #1E2F23 100%)',
        'kraft-gradient': 'linear-gradient(135deg, #D9C29A 0%, #A88F66 100%)',
      },
      transitionProperty: {
        'colors-shadows': 'background-color, border-color, color, box-shadow',
      },
      borderRadius: {
        'xl': '1rem',
        '3xl': '1.5rem',
      }
    },
  },
  plugins: [],
}
