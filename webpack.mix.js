let mix = require('laravel-mix');
let { env } = require('minimist')(process.argv.slice(2));
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

mix.setPublicPath('public');

// 主题 - 怀旧港剧
// require(`${__dirname}/resources/themes/huaijiugangju/webpack.mix.js`);

// breeze.css
mix.sass('resources/assets/sass/app.scss', 'public/css');
mix.sass('resources/assets/sass/simditor/simditor.scss', 'public/css');
mix.styles(
    ['public/css/app.css', 'public/fonts/iconfont.css', 'public/css/simditor.css'],
    'public/css/breeze.css'
).version();

//breeze.js
mix.babel('resources/assets/js/plugins/poster.js', 'public/js/poster.js');
mix.js('resources/assets/js/app.js', 'public/js/app.js');
mix.scripts(
    [
        'public/js/app.js',
        'public/js/poster.js',
        'node_modules/hls.js/dist/hls.js',
        'resources/assets/js/plugins/bootstrap-tagsinput.js',
        'resources/assets/js/plugins/at.js',
        'resources/assets/js/plugins/jquery-form.js',
        'resources/assets/js/plugins/jquery.caret.js',
    ],
    'public/js/breeze.js'
).version();

// mix.browserSync('localhost:8000');
