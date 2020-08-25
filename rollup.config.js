// @ts-check
import glob from 'fast-glob'
import typescript from 'rollup-plugin-typescript2'
import nodeResolve from '@rollup/plugin-node-resolve'
import commonjs from '@rollup/plugin-commonjs'
import { terser } from 'rollup-plugin-terser'

const isDev = process.env.NODE_ENV === 'development'

/**
 * @param {string} name
 * @param {string} path
 *
 * @returns {import('rollup').RollupOptions}
 */
function makeConfig(name, path) {
  return {
    input: { [name]: path },
    output: {
      dir: './plugins',
      format: 'iife',
      globals: {
        'blessing-skin': 'blessing',
        react: 'React',
        'react-dom': 'ReactDOM',
      },
      indent: '  ',
    },
    external: ['react', 'react-dom', 'blessing-skin'],
    plugins: [
      typescript({ check: false }),
      nodeResolve({ browser: true }),
      commonjs(),
      !isDev && terser(),
    ],
  }
}

export default glob
  .sync(['plugins/*/assets/**/*.ts', 'plugins/*/assets/**/*.tsx', '!**/*.d.ts'])
  .map((file) => {
    const name = file.replace('plugins/', '').replace(/\.tsx?$/g, '')

    return makeConfig(name, file)
  })
