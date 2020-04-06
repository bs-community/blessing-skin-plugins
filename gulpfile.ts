import { src, dest, watch } from 'gulp'

export function copy() {
  const destPath = process.env.DEST || '../blessing-skin-server/public/plugins'
  return src('*/assets/**/*.*').pipe(dest(destPath))
}

export function dev() {
  watch('*/assets/**/*.*', { ignoreInitial: false, ignored: ['**/*.ts'] }, copy)
}
