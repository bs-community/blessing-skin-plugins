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
        return file != ".git" && file != "dist" && fs.statSync(path.join(dir, file)).isDirectory();
    });
}

// release
gulp.task('release', () => {

    let folders = getFolders(pluginsPath);

    let tasks = folders.map(function (folder) {
        console.log('Zipping ' + folder);

        return gulp.src(folder + '/**/*', { base: pluginsPath })
                    .pipe(zip(folder + '.zip'))
                    .pipe(gulp.dest(distPath));
    });

    return merge(tasks);
});
