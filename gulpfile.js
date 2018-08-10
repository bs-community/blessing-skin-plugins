'use strict';

const
    chalk    = require('chalk'),
    crypto   = require('crypto'),
    execSync = require('child_process').execSync,
    fs       = require('fs'),
    gulp     = require('gulp'),
    merge    = require('merge-stream'),
    path     = require('path'),
    yaml     = require('js-yaml'),
    zip      = require('gulp-zip');

const distPath    = '.dist';
const pluginsPath = './';
const excludePath = [distPath, '.git', '.travis', 'node_modules'];
const archiveRepoBaseUrl = 'https://coding.net/u/printempw/p/bs-plugins-archive/git/raw/master/';
const yamlTranslationTemp = {};

function getPluginFolders(dir) {
    return fs.readdirSync(dir).filter(
        filename => (
            fs.statSync(path.join(dir, filename)).isDirectory() &&
            fs.existsSync(path.join(dir, filename, 'package.json')) &&
            !excludePath.includes(filename)
        )
    );
}

gulp.task('release', () => {
    let folders = getPluginFolders(pluginsPath);

    let tasks = folders.map(folder => {
        let version = require(`./${folder}/package.json`).version;
        let archiveFileName = `${folder}_v${version}.zip`;

        let gulpStream = gulp.src([
            `${folder}/**/*`,
            `!${folder}/node_modules`,
            `!${folder}/node_modules/**`,
        ], { base: pluginsPath });

        if (fs.existsSync(path.join(distPath, archiveFileName))) {
            console.log(`[${chalk.cyan(folder)}][${chalk.green(version)}] no change detected, skipping`);

            return gulpStream;
        }

        console.log(`[${chalk.cyan(folder)}][${chalk.yellow(version)}] version change detected, processing`);

        return gulpStream
                .pipe(zip(archiveFileName))
                .pipe(gulp.dest(distPath));
    });

    return merge(tasks);
});

gulp.task('build', () => {

    getPluginFolders(pluginsPath).map(folder => {
        let packageInfo = require(`./${folder}/package.json`);
        let archiveFileName = `${folder}_v${packageInfo.version}.zip`;

        if (fs.existsSync(path.join(distPath, archiveFileName))) {
            return;
        }

        let execOption = {
            cwd: path.join(process.cwd(), folder),
            stdio: 'inherit'
        }

        let prefix = `[${chalk.cyan(folder)}][${chalk.yellow(packageInfo.version)}]`;

        // Composer
        if (fs.existsSync(path.join(folder, 'composer.json'))) {
            console.log(`${prefix} installing composer packages`);

            execSync('composer install', execOption);
        }

        // Yarn or Npm
        if (fs.existsSync(path.join(folder, 'yarn.lock'))) {
            console.log(`${prefix} installing yarn packages`);

            execSync('yarn', execOption);
        } else if (fs.existsSync(path.join(folder, 'package-lock.json'))) {
            console.log(`${prefix} installing npm packages`);

            execSync('npm install', execOption);
        }

        // Exec npm build script
        if (packageInfo.scripts && packageInfo.scripts.build) {
            console.log(`${prefix} executing build script`);

            execSync('npm run build', execOption);
        }
    });
});

gulp.task('publish', () => {
    let packages = [];

    getPluginFolders(pluginsPath).map(folder => {
        let packageInfo = require(`./${folder}/package.json`);
        const keeps = ['name', 'version', 'title', 'description', 'author', 'require'];

        // Remove unnecessary fields
        for (const key in packageInfo) {
            if (! keeps.includes(key)) {
                delete packageInfo[key];
            }
        }

        // Parse i18n plugin title & description
        if (packageInfo.title.includes('::')) {
            packageInfo.title = trans(folder, packageInfo.title, 'zh_CN');
        }

        if (packageInfo.description.includes('::')) {
            packageInfo.description = trans(folder, packageInfo.description, 'zh_CN');
        }

        let archiveFileName = `${folder}_v${packageInfo.version}.zip`;
        let shasum = crypto.createHash('sha1').update(
            fs.readFileSync(path.join(distPath, archiveFileName)
        )).digest('hex');

        packageInfo['dist'] = {
            type: 'zip',
            url: archiveRepoBaseUrl + archiveFileName,
            shasum: shasum
        };

        packages.push(packageInfo);
    });

    const pluginsJson = {
        "//": [
            "This file provides metadata of all plugins available for Blessing Skin",
            "Publish your plugin at https://github.com/bs-community/blessing-skin-plugins",
            "This file is @generated automatically"
        ],
        "packages": packages
    };

    const pluginsJsonPath = path.join(distPath, 'plugins.json');
    fs.writeFileSync(pluginsJsonPath, JSON.stringify(pluginsJson, null, '  '), 'utf8');

    console.log(`Plugins metadata updated: ${ chalk.underline.green(pluginsJsonPath) }`);
});

function trans(plugin, key, locale) {
    const realKey = key.split('::')[1];
    const yamlPath = `./${plugin}/lang/${locale}/${realKey.split('.')[0]}.yml`;

    let temp;

    try {
        // Load translation strings from YAML file
        if (yamlTranslationTemp[yamlPath] !== undefined) {
            temp = yamlTranslationTemp[yamlPath];
        } else {
            temp = yaml.safeLoad(fs.readFileSync(yamlPath, 'utf8'));
            yamlTranslationTemp[yamlPath] = temp;
        }
    } catch (e) {
        console.error(e);
        return key;
    }

    const segments = realKey.split('.').slice(1);

    for (const i in segments) {
        if (temp[segments[i]] === undefined) {
            return key;
        } else {
            temp = temp[segments[i]];
        }
    }

    return temp;
}
