/**
 * @file
 * gulp tasks.
 */

(function () {
  'use strict';

  var gulp = require('gulp'),
    sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    rename = require('gulp-rename'),
    path = require('path');

  gulp.task('sass', function () {
    return gulp
      .src('*.scss')
      .pipe(sourcemaps.init())
      .pipe(sass({
        outputStyle: 'uncompressed'
      }).on('error', sass.logError))
      .pipe(sourcemaps.write('./'))
      .pipe(gulp.dest('.'));
  });

})();
