let mix = require('laravel-mix');
const path = require('path');

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

mix.webpackConfig({
  output: {
    publicPath: "/",
    chunkFilename: 'js/[name].[chunkhash].js'
  },
  resolve: {
    alias: {
      'components': 'assets/js/components',
      'modules': 'assets/js/modules',
      'config': 'assets/js/config',
      'utils': 'assets/js/utils',
      'plugins': 'assets/js/plugins',
      'runtime': 'assets/js/runtime',
    },
    modules: [
      'node_modules',
      path.resolve(__dirname, "resources")
    ]
  },
})

mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css')
   .version();

mix.sass('resources/assets/sass/wework.scss', 'public/css');

// mix.js('resources/assets/js/socket.js', 'public/js')
//    .version();
mix.js('resources/assets/js/canceler.js', 'public/js')
   .version();
mix.browserSync({
  //proxy: 'www.istore.com',
  proxy: 'istore.ngrok.zark.in',
  files: [
    'resources/views/**/*.php',
    'public/assets/merchant/**/*.js',
    'public/assets/merchant/**/*.css'
  ]
});
