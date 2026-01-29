const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .vue(3) // Specify Vue 3
    .postCss('resources/css/app.css', 'public/css', [
        require('tailwindcss'),
    ])
    .setPublicPath('public'); // Explicitly set public path

if (mix.inProduction()) {
    mix.version();
}

// If you have other entry points or configurations, add them here
// For example, if you have a separate admin CSS/JS:
// mix.js('resources/js/admin.js', 'public/js/admin')
//    .postCss('resources/css/admin.css', 'public/css/admin');

mix.alias({
    '@': 'resources/js',
});

// Enable source maps for development
if (!mix.inProduction()) {
    mix.sourceMaps();
}
