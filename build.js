const fs = require('fs')
const childProcess = require('child_process')
const crypto = require('crypto')
const yaml = require('js-yaml')

function trans(plugin, key, locale) {
  const realKey = key.split('::')[1]
  const yamlPath = `./${plugin}/lang/${locale}/${realKey.split('.')[0]}.yml`

  let temp = yaml.safeLoad(fs.readFileSync(yamlPath, 'utf8'))

  const segments = realKey.split('.').slice(1)

  for (const seg of segments) {
    if (temp[seg]) {
      temp = temp[seg]
    } else {
      return key
    }
  }

  return temp
}

const { packages } = JSON.parse(fs.readFileSync('./.dist/registry.json', 'utf-8'))

const plugins = fs
  .readdirSync('.')
  .filter(name => !name.startsWith('.') && name !== 'node_modules')
  .filter(name => fs.statSync(name).isDirectory())

plugins.forEach(name => {
  process.chdir(name)

  const { scripts, version } = require(`./${name}/package.json`)

  let package
  if ((package = packages.find(pkg => pkg.name === name)) && package.version === version) {
    console.log(`[${name}] Version not bumped. Skip building.`)
    process.chdir(__dirname)
    return
  }
  console.log(`[${name}] Building...`)

  if (scripts && scripts.build) {
    childProcess.spawnSync('yarn', ['build'])
  }

  childProcess.execSync('rm -rf node_modules assets/src')
  try {
    fs.unlinkSync('.gitignore')
  } catch {}

  try {
    fs.statSync('composer.json')
    childProcess.spawnSync('composer', ['install'])
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
        url: `https://dev.azure.com/blessing-skin/0dc12c60-882a-46a2-90c6-9450490193a2/_apis/git/repositories/d5283b63-dfb0-497e-ad17-2860a547596f/Items?path=%2F${name}_${manifest.version}.zip`,
        shasum: crypto
          .createHash('sha1')
          .update(fs.readFileSync(`.dist/${name}_${manifest.version}.zip`))
          .digest('hex')
      }
    }
  })
}

fs.writeFileSync('.dist/registry.json', JSON.stringify(meta))
