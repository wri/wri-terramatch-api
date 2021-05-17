const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.copy("node_modules/swagger-ui-dist/swagger-ui.css", "public/css/swagger-ui.css");
mix.copy("node_modules/swagger-ui-dist/swagger-ui-bundle.js", "public/js/swagger-ui-bundle.js");
