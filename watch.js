const fs = require('fs')
const dirname = require('path').dirname
const chokidar = require('chokidar')
const mkdirp = require('mkdirp')
const args = require('minimist')(process.argv.slice(2))

const dest = args.dest || '../blessing-skin-server/public/plugins'

chokidar
  .watch('**/assets/**/*', {
    ignored: [/(^|[\/\\])\../, '**/assets/src'],
    ignoreInitial: true,
  })
  .on('all', (_, path) => {
    try {
      fs.accessSync(path)
    } catch (_) {
      fs.unlink(`${dest}/${path}`, err => err && console.error(err))
      return
    }

    const dir = `${dest}/${dirname(path)}`
    try {
      fs.accessSync(dir, fs.constants.F_OK | fs.constants.W_OK)
    } catch (error) {
      mkdirp.sync(dir)
    }
    fs.copyFile(path, `${dest}/${path}`, err => err && console.error(err))
  })
