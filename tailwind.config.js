/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    // Tailwind v4 is configured CSS-first in resources/css/app.css.
    // This file only preserves explicit content paths for compatible tooling.
};
