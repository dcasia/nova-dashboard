const mix = require('laravel-mix')
const path = require('path')

mix.setPublicPath('dist')
   .js('resources/js/tool.js', 'js')
   .vue()
   .webpackConfig(webpack => {
       return {
           resolve: {
               alias: {
                   'lodash': path.resolve(__dirname, './node_modules/lodash/lodash.js'),
                   '@babel/runtime/regenerator': path.resolve(__dirname, './node_modules/@babel/runtime/regenerator/index.js'),
                   '@': path.resolve(__dirname, '../vendor/laravel/nova/resources/js/'),
                   '~~nova~~': path.resolve(__dirname, '../vendor/laravel/nova/resources/js/')
               }
           },
       }
   })
