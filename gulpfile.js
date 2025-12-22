const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');

// Paths
const paths = {
	styles: {
		src: 'assets/css/**/*.scss',
		dest: 'assets/css/',
	},
};

// Compile SCSS to CSS
function styles() {
	return gulp
		.src(paths.styles.src)
		.pipe(sass().on('error', sass.logError))
		.pipe(gulp.dest(paths.styles.dest))
		.pipe(cleanCSS())
		.pipe(rename({ suffix: '.min' }))
		.pipe(gulp.dest(paths.styles.dest));
}

// Watch files
function watch() {
	gulp.watch(paths.styles.src, styles);
}

// Export tasks
exports.styles = styles;
exports.watch = watch;
exports.default = gulp.series(styles);
