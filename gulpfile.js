'use strict';

const gulp     = require('gulp'),
      fs       = require('fs'),
      path     = require('path'),
      merge    = require('merge-stream'),
      zip      = require('gulp-zip'),
      execSync = require("child_process").execSync;

const distPath    = '.dist';
const pluginsPath = './';
const excludePath = [
    distPath,
    ".git",
    ".travis",
    "node_modules"
];

function getPluginFolders(dir) {
    return fs.readdirSync(dir).filter(
        filename => (fs.statSync(path.join(dir, filename)).isDirectory() && !excludePath.includes(filename))
    );
}

gulp.task('release', () => {
    let folders = getPluginFolders(pluginsPath);

    let tasks = folders.map(folder => {
        let version = require(`./${folder}/package.json`).version;
        let archiveFileName = `${folder}_v${version}.zip`;

        let gulpStream = gulp.src(folder + '/**/*', { base: pluginsPath });

        if (fs.existsSync(path.join(distPath, archiveFileName))) {
            console.log(`[${folder}][${version}] no change detected, skipping`);

            return gulpStream;
        }

        console.log(`[${folder}][${version}] version change detected, processing`);

        if (fs.existsSync(path.join(folder, 'composer.json'))) {
            console.log(`[${folder}][${version}] installing composer packages`);

            execSync('composer install', {
                cwd: path.join(process.cwd(), folder)
            });
        }

        return gulpStream
                .pipe(zip(archiveFileName))
                .pipe(gulp.dest(distPath));
    });

    return merge(tasks);
});
