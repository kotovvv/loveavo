const mix = require("laravel-mix");

const LiveReloadPlugin = require("webpack-livereload-plugin");

const VuetifyLoaderPlugin = require("vuetify-loader/lib/plugin");

var webpackConfig = {
  module: {
    rules: [
      {
        test: /\.js?$/,
        use: [
          {
            loader: "babel-loader",
            // options: mix.config.babel()
          },
        ],
      },
    ],
  },
  plugins: [new LiveReloadPlugin(), new VuetifyLoaderPlugin()],
};

mix.webpackConfig(webpackConfig);

mix
  .js("resources/js/app.js", "public/js")
  //   .vuetify("vuetify-loader", {
  //     extract: "css/vuetify-components.css",
  //   })
  .vue()
  .sass("resources/sass/app.scss", "public/css");
// mix.js('resources/js/admin.js', 'public/js')
//     .vue();
