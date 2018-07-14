'use strict';

const gulp     = require('gulp'),
      fs       = require('fs'),
      path     = require('path'),
      chalk    = require('chalk'),
      merge    = require('merge-stream'),
      zip      = require('gulp-zip'),
      execSync = require('child_process').execSync;

const distPath    = '.dist';
const pluginsPath = './';
const excludePath = [
    distPath,
    '.git',
    '.travis',
    'node_modules'
];

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

        buildPlugin(folder);

        return gulpStream
                .pipe(zip(archiveFileName))
                .pipe(gulp.dest(distPath));
    });

    return merge(tasks);
});

function buildPlugin(folder) {
    let packageInfo = require(`./${folder}/package.json`);
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
}
