import { src, dest, watch } from 'gulp'

export function copy() {
  return src('*/assets/**/*.*').pipe(dest('/tmp/plugins'))
}

export function dev() {
  watch('*/assets/**/*.*', copy)
}
