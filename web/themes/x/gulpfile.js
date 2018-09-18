/**
 *
 * Gulp file to build the theme
 *
 */

// Configuration object
var config = {};

config.patternLab = {
  dir: './dist',
  patternDir: './dist/source/_patterns',
  publicCssDir: './dist/public/css',
  metaDir: './dist/source/_meta/',
  headFilename: '_00-head.twig',
  watchFiles: [
    './dist/source/_patterns/**/*.twig',
    './dist/source/_patterns/**/*.json',
    './dist/source/_patterns/**/*.md',
    './dist/source/_patterns/**/*.yml'
  ],
};

config.styles = {
  input_combined: [
    config.patternLab.dir + '/source/scss/**/*.scss',
  ],
  input_individual: [
    config.patternLab.patternDir + '/**/*.scss',
  ],
  output: config.patternLab.dir + '/source/css/',
  watchFiles: [
    config.patternLab.dir + '/source/scss/**/*.scss',
    config.patternLab.patternDir + '/**/*.scss',
  ]
};

config.browserSync = {
  server: {
    baseDir: config.patternLab.dir + '/public'
  },
  proxy: {
    target: '',
    reqHeaders: {
      host: ''
    }
  },
  open: false
};

config.svgs = {
  input: 'svg/**/*.svg',
  output: 'dist/svg/',
  output_filename: 'icons.svg',
  inline_input: 'svg/icons/',
  inline: 'images/inline'
};

var gulp = require('gulp');
var sass = require('gulp-sass');
var sassLint = require('gulp-sass-lint');
var postcss = require('gulp-postcss');
var autoprefixer = require('autoprefixer');
var cleanCSS = require('gulp-clean-css');
var browserSync = require('browser-sync').create();
var runSequence = require('run-sequence');
var sourcemaps = require('gulp-sourcemaps');
var rename = require('gulp-rename');
var run = require('gulp-run');
var del = require('del');
var log = require('fancy-log');
var svgmin = require('gulp-svgmin');
var svgstore = require('gulp-svgstore');
var cheerio = require('gulp-cheerio');
var fs = require('fs');
var rtl = require('postcss-rtl');
var tap = require('gulp-tap');
var argv = require('yargs').argv;

var postCSSProcessors = [
  // rtl,  //commented out for now as it makes the CSS hard to inspect
  autoprefixer({ browsers: ['last 2 versions'] })
]

gulp.task('lint', ['lint:styles']);
gulp.task('lint:styles', ['lint:sass:combined', 'lint:sass:patterns']);
gulp.task('build:styles', ['build:styles:combined', 'build:styles:individual']);


gulp.task('lint:sass:combined', function () {
  return gulp.src(config.styles.input_combined)
  // use gulp-cached to check only modified files.
      .pipe(sassLint({configFile: 'lint.yml', files: {ignore: ['**/patternlab-scaffolding.scss', '**/normalize/*.scss']}}))
      .pipe(sassLint.format())
      .pipe(sassLint.failOnError())
});

gulp.task('lint:sass:patterns', function () {
  return gulp.src(config.patternLab.patternDir + '/**/*.scss')
  // use gulp-cached to check only modified files.
      .pipe(sassLint({configFile: 'lint.yml'}))
      .pipe(sassLint.format())
      .pipe(sassLint.failOnError())
});

gulp.task('build:styles:combined', function () {
  return build_styles (config.styles.input_combined);
});

gulp.task('build:styles:individual', function () {
  return build_styles (config.styles.input_individual, 'patterns');
});

function build_styles (source_files, destination_subfolder = '') {
  return gulp.src(source_files)
      .pipe(sourcemaps.init())
      .pipe(sass().on('error', sass.logError))
      .pipe(postcss(postCSSProcessors))
      // .pipe(cleanCSS({compatibility: 'ie8'}))
      .pipe(sourcemaps.write('./'))
      .pipe(rename({dirname: destination_subfolder}))
      .pipe(gulp.dest(config.styles.output))
      .pipe(browserSync.stream({match: '**/*.css'}));
}

/**
 * Calls Browsersync reload.
 */
gulp.task('bs:reload', function () {
  browserSync.reload();
});

/**
 * Copies CSS files to Pattern Lab's public dir.
 */
gulp.task('build:copy-css', function () {
  return gulp.src(config.styles.output + '/**/*.css')
      .pipe(gulp.dest(config.patternLab.publicCssDir))
      .pipe(browserSync.stream());
});

/**
 * PatternLab task runners
 */
gulp.task('patternlab:generate', function () {
  return run('php ' + config.patternLab.dir + '/core/console --generate').exec();
});



/**
 * Backstop task runners
 */
gulp.task('backstop:test', function () {
  run_backstop_task('test');
});

gulp.task('backstop:approve', function () {
    run_backstop_task('approve');
});


function run_backstop_task(task) {
    if (argv.patterns) {
        patterns = argv.patterns.split(',');
        patterns.forEach(function(pattern) {
            log('Running backstop ' + task + ' for ' + pattern + '.');
            var cmd = new run.Command('./node_modules/backstopjs/cli/index.js ' + task +' --config=backstop.config.js --filter=' + pattern);
            cmd.exec();
        });
    } else {
        log('Running ' + task + ' for all patterns.');
        var cmd = new run.Command('./node_modules/backstopjs/cli/index.js ' + task +' --config=backstop.config.js');
        cmd.exec();
    }
}

/**
 * Removes the built svg file
 */
gulp.task('clean:svgs', function () {
  del.sync([
    config.svgs.output
  ]);
});

/**
 * Removes the css files
 */
gulp.task('clean:styles', function () {
  del.sync([
    config.styles.output
  ]);
});

/**
 * Remove and rebuild the svg file
 */
gulp.task('build:svgs', ['clean:svgs'], function () {
  return gulp.src(config.svgs.input)
      .pipe(tap(function (file, t) {
          return gulp.src(file.path)
              .pipe(rename({prefix: 'rpl-svg-'}))
              .pipe(svgmin())
              .pipe(svgstore({inlineSvg: true}))
              .pipe(rename({ basename: file.relative, extname: '', prefix: 'symbol-'}))
              .pipe(cheerio(function ($, file) {
                $('svg').replaceWith($('svg').children());
              }, { decodeEntities: false }))
              .pipe(gulp.dest(config.svgs.output + 'individuals/'));
      }))
      .pipe(rename({prefix: 'rpl-svg-'}))
      .pipe(svgmin())
      .pipe(svgstore({inlineSvg: true}))
      .pipe(rename(config.svgs.output_filename))
      .pipe(gulp.dest(config.svgs.output));
});

/**
 * Insert SVG file into PatternLab header
 */
gulp.task('patternlab:grab-svgs', function () {
  return gulp.src(config.patternLab.metaDir + config.patternLab.headFilename)
      .pipe(cheerio(function ($, file) {
        var svgs = fs.readFileSync(config.svgs.output + config.svgs.output_filename, "utf8");
        $('#do-not-replace-see-gulpfile').find('svg').replaceWith(svgs);
      }, { decodeEntities: false }))
      .pipe(gulp.dest(config.patternLab.metaDir));
});

/**
 * Task sequence to run when pattern files have changed.
 */
gulp.task('patterns-change', function () {
  runSequence('patternlab:generate', 'bs:reload');
});

/**
 * Task sequence to run when SVG files have changed
 */
gulp.task('svgs-change', function () {
  runSequence('build:svgs', 'patternlab:grab-svgs');
});

/**
 * Task sequence to run when Sass files have changed.
 */
gulp.task('sass-change', function () {
  runSequence(['build:styles', 'lint:styles'], 'build:copy-css');
});

// Watch Files For Changes
gulp.task('watch', function() {

  runSequence('patterns-change', 'sass-change', 'bs:reload');

  if (config.browserSync.proxy.target) {
    browserSync.init({
      proxy: config.browserSync.proxy,
      open: config.browserSync.open,
      notify: false
    });
  }
  else {
    browserSync.init({
      server: config.browserSync.server,
      open: config.browserSync.open,
      notify: false
    });
  }
  gulp.watch(config.styles.watchFiles, ['sass-change']);
  gulp.watch(config.patternLab.watchFiles, ['patterns-change']);
  gulp.watch(config.svgs.input, ['svgs-change']);
});

gulp.task('clean', ['clean:styles','clean:svgs']);

// Default Task
gulp.task('default', ['watch']);