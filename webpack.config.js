const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require("extract-text-webpack-plugin");
const nodeEnv = (process.env.NODE_ENV || 'development').trim();

// eslint-disable-next-line
const __DEV__ = nodeEnv !== 'production';

const devtool = __DEV__ ? '#source-map' : '';

const plugins = [
  new webpack.DefinePlugin({
    'process.env': {
      NODE_ENV: JSON.stringify(nodeEnv),
    },
  }),
  new ExtractTextPlugin('[name].css'),
];

// if (!__DEV__) {
//   plugins.push(
//     new webpack.optimize.UglifyJsPlugin({
//       compress: {
//         warnings: false,
//       },
//       output: {
//         comments: false,
//       },
//       screwIe8: true,
//       sourceMap: false,
//     })
//   );
// }

const themeEntries = {};
for (let i = 0; i < 7; i++) {
  themeEntries[`theme${i}`] = `./themes/theme${i}.less`;
}

module.exports = {
  context: __dirname,
  resolve: {
    extensions: ['.js', '.jsx'],
  },
  entry: {
    ...themeEntries,
      vendor: './js/vendor.js',
  },
  output: {
    path: path.resolve('public/assets'),
    filename: '[name].js',
    publicPath: '/assets/',
  },
  module: {
    rules: [
      {
        test: /\.jsx?$/,
        exclude: /(node_modules)/,
        loader: 'babel-loader',
        query: { cacheDirectory: true },
      },
      { test: /\.(eot|ttf|otf|svg|woff2?)(\?.*)?$/, loader: 'file-loader' },
      { test: /\.json$/, loader: 'json-loader' },
      { test: /\.css$/, loader: 'style-loader!css-loader' },
      { test: /\.less$/, use: ExtractTextPlugin.extract({
          fallback: 'style-loader',
          use: 'css-loader!less-loader'
        })}
    ],
  },
  plugins,
  devtool,
};
