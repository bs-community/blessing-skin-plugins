import glob from 'fast-glob'
import { defineConfig } from 'rollup'
import replace from '@rollup/plugin-replace'
import resolve from '@rollup/plugin-node-resolve'
import commonjs from '@rollup/plugin-commonjs'
import { swc } from 'rollup-plugin-swc3'
import svelte from 'rollup-plugin-svelte'
import sveltePreprocess from 'svelte-preprocess'

const isDev = process.env.NODE_ENV === 'development'

function makeConfig(name: string, path: string) {
  return defineConfig({
    input: { [name]: path },
    output: {
      dir: './plugins',
      format: 'esm',
    },
    plugins: [
      replace({
        values: {
          'process.env.NODE_ENV': isDev ? '"development"' : '"production"',
        },
        preventAssignment: true,
      }),
      swc({
        jsc: {
          target: 'es2020',
          minify: isDev
            ? undefined
            : {
                compress: {},
                mangle: {},
              },
        },
        minify: !isDev,
      }),
      svelte({
        preprocess: sveltePreprocess(),
        emitCss: false,
      }),
      resolve({ browser: true }),
      commonjs(),
    ],
  })
}

export default glob([
  'plugins/*/assets/**/*.ts',
  '!plugins/*/assets/**/*.test.ts',
  '!**/*.d.ts',
]).then((files) =>
  files.map((file) =>
    makeConfig(file.replace('plugins/', '').replace(/\.ts$/g, ''), file),
  ),
)
