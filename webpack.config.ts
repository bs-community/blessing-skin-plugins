import glob from 'fast-glob'
import type { Configuration } from 'webpack'

const config: Configuration = {
  mode: 'production',
  entry: async () => {
    const files = await glob(['*/assets/**/*.ts', '*/assets/**/*.tsx'], {
      ignore: ['node_modules'],
    })

    // @ts-ignore
    return Object.fromEntries(
      files
        .filter((file) => !file.endsWith('.d.ts'))
        .map((file) => [file.replace(/\.tsx?$/g, ''), `./${file}`]),
    )
  },
  output: {
    path: __dirname,
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
          compilerOptions: {
            module: 'es2015',
          },
        },
      },
    ],
  },
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM',
  },
}

export default config
