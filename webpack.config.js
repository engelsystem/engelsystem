const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const nodeEnv = (process.env.NODE_ENV || 'development').trim();

// eslint-disable-next-line
const __DEV__ = nodeEnv !== 'production';

const devtool = __DEV__ ? 'source-map' : undefined 

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
for (let i = 0; i <= 15; i++) {
  themeEntries[`theme${i}`] = `./resources/assets/themes/theme${i}.scss`;
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
    minimizer: __DEV__ ? [] : [new CssMinimizerPlugin(), new TerserPlugin()],
  },
  module: {
    rules: [
      {
        test: /\.jsx?$/,
        exclude: /(node_modules)/,
        loader: 'babel-loader',
      },
      { test: /\.(jpg|eot|ttf|otf|svg|woff2?)(\?.*)?$/, loader: 'file-loader' },
      { test: /\.json$/, loader: 'json-loader' },
      {
        test: /\.(scss|css)$/,
        use: [
          { loader: MiniCssExtractPlugin.loader },
          { loader: 'css-loader', options: { importLoaders: 1 } },
          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: [ [ 'autoprefixer', ], ],
              },
            },
          },
          { 
              loader: 'sass-loader',
              options: {
                  sassOptions: {
                      quietDeps: true
                  }
              }
          },
        ]
      }
    ],
  },
  plugins,
  devtool,
};
