const fs = require('fs')
const childProcess = require('child_process')
const crypto = require('crypto')

function trans(plugin, key, locale) {
  const realKey = key.split('::')[1]
  const yamlPath = `./${plugin}/lang/${locale}/${realKey.split('.')[0]}.yml`

  let temp = yaml.safeLoad(fs.readFileSync(yamlPath, 'utf8'))

  const segments = realKey.split('.').slice(1)

  for (const seg of segments) {
    if (temp[seg]) {
      return key
    } else {
      temp = temp[seg]
    }
  }

  return temp
}

const plugins = fs
  .readdirSync('.')
  .filter(name => !name.startsWith('.') && name !== 'node_modules')
  .filter(name => fs.statSync(name).isDirectory())

plugins.forEach(name => {
  process.chdir(name)

  try {
    fs.statSync('composer.json')
    childProcess.spawnSync('composer', ['install'])
  } catch {}

  const { scripts, version } = require(`./${name}/package.json`)
  if (scripts && scripts.build) {
    childProcess.spawnSync('yarn', ['build'])
  }

  childProcess.execSync('rm -rf node_modules assets/src')
  try {
    fs.unlinkSync('.gitignore')
  } catch {}

  process.chdir(__dirname)

  childProcess.execSync(`zip -9 -r .dist/${name}_${version}.zip ${name}`)
})

const meta = {
  version: 1,
  packages: plugins.map(name => {
    const manifest = require(`./${name}/package.json`)
    return {
      name,
      version: manifest.version,
      title: manifest.title.includes('::')
        ? trans(name, manifest.title, 'zh_CN')
        : manifest.title,
      description: manifest.description.includes('::')
        ? trans(name, manifest.description, 'zh_CN')
        : manifest.description,
      author: manifest.author,
      require: manifest.require,
      dist: {
        type: 'zip',
        url: '',
        shasum: crypto
          .createHash('sha1')
          .update(fs.readFileSync(`.dist/${name}_${version}.zip`))
          .digest('hex')
      }
    }
  })
}

fs.writeFileSync('.dist/registry.json', JSON.stringify(meta))
