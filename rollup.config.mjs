// @ts-check
import glob from 'fast-glob'
import typescript from '@rollup/plugin-typescript'
import replace from '@rollup/plugin-replace'
import resolve from '@rollup/plugin-node-resolve'
import commonjs from '@rollup/plugin-commonjs'
import { terser } from 'rollup-plugin-terser'
import svelte from 'rollup-plugin-svelte'
import sveltePreprocess from 'svelte-preprocess'

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
      replace({
        values: {
          'process.env.NODE_ENV': isDev ? '"development"' : '"production"',
        },
        preventAssignment: true,
      }),
      typescript(),
      svelte({
        preprocess: sveltePreprocess(),
        emitCss: false,
      }),
      resolve({ browser: true }),
      commonjs(),
      !isDev && terser(),
    ],
  }
}

export default glob([
  'plugins/*/assets/**/*.ts',
  'plugins/*/assets/**/*.tsx',
  '!plugins/*/assets/**/*.test.ts',
  '!**/*.d.ts',
]).then((files) =>
  files.map((file) => {
    const name = file.replace('plugins/', '').replace(/\.tsx?$/g, '')

    return makeConfig(name, file)
  }),
)
