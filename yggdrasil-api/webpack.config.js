const VueLoaderPlugin = require('vue-loader/lib/plugin')

module.exports = [
  {
    mode: 'production',
    entry: './assets/src/dnd.js',
    output: {
      path: __dirname + '/assets/dist',
      filename: 'dnd.js'
    }
  },
  {
    mode: 'production',
    entry: './assets/src/log.js',
    output: {
      path: __dirname + '/assets/dist',
      filename: 'log.js'
    },
    module: {
      rules: [
        {
          test: /\.vue$/,
          loader: 'vue-loader'
        },
        {
          test: /\.js$/,
          loader: 'babel-loader'
        },
        {
          test: /\.css$/,
          use: [
            'style-loader',
            'css-loader'
          ]
        }
      ]
    },
    plugins: [
      new VueLoaderPlugin()
    ]
  }
]
