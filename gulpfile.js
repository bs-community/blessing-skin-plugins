/*
* @Author: printempw
* @Date:   2017-01-22 17:25:14
* @Last Modified by:   printempw
* @Last Modified time: 2017-01-22 17:52:40
*/

'use strict';

const gulp  = require('gulp'),
      del   = require('del'),
      fs    = require('fs'),
      path  = require('path'),
      merge = require('merge-stream'),
      zip   = require('gulp-zip');

const pluginsPath = './';
const distPath    = './dist/';

function getFolders(dir) {
    return fs.readdirSync(dir).filter((file) => {
        return file != ".git" &&
               file != "dist" &&
               file != "node_modules" &&
               fs.statSync(path.join(dir, file)).isDirectory();
    });
}

// release
gulp.task('release', () => {
    del(['dist']);

    let folders = getFolders(pluginsPath);

    let tasks = folders.map((folder) => {
        let version = require(`./${folder}/package.json`).version;

        console.log(`Zipping plugin ${folder}, version ${version}`);

        return gulp.src(folder + '/**/*', { base: pluginsPath })
                    .pipe(zip( `${folder}_v${version}.zip`))
                    .pipe(gulp.dest(distPath));
    });

    return merge(tasks);
});
