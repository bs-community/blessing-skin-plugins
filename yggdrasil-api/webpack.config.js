module.exports = [
  {
    mode: 'production',
    entry: './assets/src/config.js',
    output: {
      path: __dirname + '/assets/dist',
      filename: 'config.js'
    }
  },
  {
    mode: 'production',
    entry: './assets/src/dnd.js',
    output: {
      path: __dirname + '/assets/dist',
      filename: 'dnd.js'
    }
  },
]
