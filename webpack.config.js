const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
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
  new MiniCssExtractPlugin({
    filename: '[name].css',
    chunkFilename: '[id]-[hash].css',
  }),
];


const themeEntries = {};
for (let i = 0; i < 12; i++) {
  themeEntries[`theme${i}`] = `./resources/assets/themes/theme${i}.less`;
}

module.exports = {
  mode: __DEV__ ? 'development' : 'production',
  context: __dirname,
  resolve: {
    extensions: ['.js', '.jsx'],
  },
  entry: {
    ...themeEntries,
    vendor: './resources/assets/js/vendor.js',
  },
  output: {
    path: path.resolve('public/assets'),
    filename: '[name].js',
    publicPath: '',
  },
  optimization: {
    minimizer: __DEV__ ? [] : [new OptimizeCSSAssetsPlugin({})],
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
      {
        test: /\.(less|css)$/,
        use: [
          { loader: MiniCssExtractPlugin.loader },
          { loader: 'css-loader', options: { importLoaders: 1 } },
          { loader: 'less-loader' },
        ]
      }
    ],
  },
  plugins,
  devtool,
};
