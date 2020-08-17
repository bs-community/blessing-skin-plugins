import glob from 'fast-glob'
import type { Configuration } from 'webpack'

const config: Configuration = {
  mode: 'production',
  entry: async () => {
    const files = await glob([
      'plugins/*/assets/**/*.ts',
      'plugins/*/assets/**/*.tsx',
    ])

    // @ts-ignore
    return Object.fromEntries(
      files
        .filter((file) => !file.endsWith('.d.ts'))
        .map((file) => [
          file.replace('plugins/', '').replace(/\.tsx?$/g, ''),
          `./${file}`,
        ]),
    )
  },
  output: {
    path: `${__dirname}/plugins`,
    filename: '[name].js',
    ecmaVersion: 2017,
  },
  module: {
    rules: [
      {
        test: /\.tsx?$/,
        loader: 'ts-loader',
        options: {
          transpileOnly: true,
        },
      },
      {
        test: /\.scss$/,
        use: [
          { loader: 'style-loader', options: { esModule: true } },
          { loader: 'css-loader', options: { importLoaders: 1 } },
          { loader: 'sass-loader' },
        ],
      },
    ],
  },
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM',
    'blessing-skin': 'blessing',
  },
  devtool: false,
}

export default config
