(function () {
  'use strict';

var gulp = require('gulp'),
	eslint = require('gulp-eslint'),
  sass = require('gulp-sass'),
  sourcemaps = require('gulp-sourcemaps')
	//autoprefixer = require('gulp-autoprefixer'),
	//imagemin = require('gulp-imagemin');

  gulp.task('sass', function () {
    return gulp
      .src('./scss/**/*.scss')
      .pipe(sourcemaps.init())
      .pipe(sass({
        outputStyle: 'uncompressed'
      }).on('error', sass.logError))
      .pipe(sourcemaps.write('./'))
      .pipe(gulp.dest('./css'));
  });

  gulp.task('watch', gulp.series('sass', function () {
    gulp.watch('./scss/**/*.scss', gulp.series('sass'));
  }));

})();