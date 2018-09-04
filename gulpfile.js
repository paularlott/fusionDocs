var gulp = require('gulp'),
	plugins = require('gulp-load-plugins')();

plugins.fs = require('fs');
plugins.del = require('del');

var themeTaskList = [];

/**
 * Get a list of the available themes and create the tasks
 */
var themeList = plugins.fs.readdirSync('./themes_src/');
themeList.forEach(function (theme) {

	// Create the names for the CSS and minified CSS files
	var cssfile = theme.toLowerCase();

	// Create the tasks

	// Less
	if(theme != 'common') {
		gulp.task(
			theme + '_css',
			function () {
				return gulp.src('./themes_src/' + theme + '/less/' + theme + '_theme.less')
					.pipe(plugins.less())
					.pipe(plugins.cleanCss())
					.pipe(plugins.rename(cssfile + '.css'))
					.pipe(gulp.dest('./themes/' + theme + '/css/'));
			}
		)
		themeTaskList.push(theme + '_css');
	}

	// JS
	gulp.task(
		theme + '_js',
		function() {
			return gulp.src('./themes_src/' + theme + '/js/*.js')
				.pipe(plugins.concat(theme + '.js'))
				.pipe(plugins.uglify())
				.pipe(gulp.dest('./themes/' + theme + '/js/'));
		}
	)
	themeTaskList.push(theme + '_js');
});

/**
 * Watch files for changes
 */
gulp.task('watch', function() {
	gulp.watch([
		'./themes_src/*/less/*.less',
		'./themes_src/*/js/*.js'
	], gulp.parallel(themeTaskList));
});

/**
 * Default build task
 */
gulp.task('default', gulp.parallel(themeTaskList));
