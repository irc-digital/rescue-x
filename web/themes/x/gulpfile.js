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
  iconLibraryDataFile: './dist/source/_patterns/01-atoms/57-icon-library/icon-library.yml',
  publicCssDir: './dist/public/css',
  publicJsDir: './dist/public/js',
  metaDir: './dist/source/_meta/',
  headFilename: '_00-head.twig',
  footFilename: '_01-foot.twig',
  watchFiles: [
    './dist/source/_patterns/**/*.twig',
    './dist/source/_patterns/**/*.json',
    './dist/source/_patterns/**/*.md',
    './dist/source/_patterns/**/*.yml'
  ],
};

config.styles = {
  max_file_size: '80000', // this is a bit of a safety valve - edge this up to protect us from bad includes or bad CSS blowing up our file size
  input: config.patternLab.dir + '/source/scss',
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

config.javascript = {
  drupalDependencies: [
    '../../core/assets/vendor/domready/ready.min.js',
    '../../core/assets/vendor/jquery/jquery.min.js',
    '../../core/misc/drupal.js',
    '../../core/misc/drupal.init.js',
  ],
  pattern_javascript: [
    config.patternLab.patternDir + '/**/*.js',
  ],
  jsDir: config.patternLab.dir + '/source/js',
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
  inline_input: 'svg/',
  inline_intermediate: 'dist/svg/inline'
};
config.svgs.inlined_svg = [
  //config.svgs.inline_input + 'twitter.svg'
];

const gulp = require('gulp');
const sass = require('gulp-sass');
const sassLint = require('gulp-sass-lint');
const postcss = require('gulp-postcss');
  const autoprefixer = require('autoprefixer');
const cleanCSS = require('gulp-clean-css');
const browserSync = require('browser-sync').create();
const runSequence = require('run-sequence');
const sourcemaps = require('gulp-sourcemaps');
const rename = require('gulp-rename');
const run = require('gulp-run');
const del = require('del');
const log = require('fancy-log');
const svgmin = require('gulp-svgmin');
const svgstore = require('gulp-svgstore');
const cheerio = require('gulp-cheerio');
const fs = require('fs');
const rtl = require('postcss-rtl');
const tap = require('gulp-tap');
const argv = require('yargs').argv;
const export_sass = require('node-sass-export');
const jsonToYaml = require('gulp-json-to-yaml');
const clean = require('gulp-clean');
const warn_size = require('gulp-warn-size');
const replace = require('gulp-replace');
var concat = require('gulp-concat');
var entities = require('gulp-html-entities');
var base64 = require('gulp-base64-inline');

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
      .pipe(cleanCSS({compatibility: 'ie8'}))
      .pipe(warn_size(config.styles.max_file_size))
      .on('error', () => process.exit(1))
      .pipe(sourcemaps.write('./'))
      .pipe(rename({dirname: destination_subfolder}))
      .pipe(gulp.dest(config.styles.output))
      .pipe(browserSync.stream({match: '**/*.css'}));
}

/**
 * This task copies some Drupal files that our PL needs. This is a bit of a hack.
 *
 * However, this is done both by Forum One and Mediacurrent in their decoupled
 * solutions - so it will have to suffice for a bit.
 */
gulp.task('build:javascript:drupal-copy', function () {
  return gulp.src(config.javascript.drupalDependencies)
      .pipe(gulp.dest(config.javascript.jsDir + '/' + 'core'));
});

/**
 * This task copies any js files from the pattern folder into our js source folder*
 */
gulp.task('build:javascript:pattern-copy', function () {
  return gulp.src(config.javascript.pattern_javascript)
      .pipe(rename({dirname: './'}))
      .pipe(gulp.dest(config.javascript.jsDir + '/' + 'patterns'))
});

/**
 * Copies javascript files to Pattern Lab's public dir.
 */
gulp.task('build:copy-javascript', function () {
  return gulp.src(config.javascript.jsDir + '/**/*.js')
      .pipe(gulp.dest(config.patternLab.publicJsDir));
});


gulp.task('build:javascript', function (callback) {
  return runSequence('build:javascript:drupal-copy', 'build:javascript:pattern-copy', 'patternlab:javascript', 'build:copy-javascript', callback);
});

/**
 * Removes the css files
 */
gulp.task('clean:javascript', function () {
  del.sync([
    config.javascript.jsDir
  ]);

  del.sync([
    config.patternLab.publicJsDir
  ]);

});

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
gulp.task('patternlab:generate', function (callback) {
  return run('php ' + config.patternLab.dir + '/core/console --generate').exec();
});

/**
 * We turn some SCSS variables into JSON arrays so that PatternLab can use them
 * to more dynamically geneate some stuff (e.g. like color palettes)
 */
gulp.task('patternlab:generate:variables', function (callback) {
  return runSequence('patternlab:generate:variables_as_json', 'patternlab:generate:variables_as_yml', 'patternlab:generate:concatenate_button_examples', callback);



  // del.sync([
  //   config.patternLab.dir + '/source/_patterns/**/*.json'
  // ]);

  // del([config.patternLab.dir + '/source/**/*.json'], {dryRun: true}).then(paths => {
  //   console.log('Files and folders that would be deleted:\n', paths.join('\n'));
  // });

});

gulp.task('patternlab:generate:concatenate_button_examples', function () {
  fs.writeFileSync(config.patternLab.dir + '/source/_patterns/01-atoms/31-button-examples/button-examples.yml', "");

  return gulp.src(config.patternLab.dir + '/source/_patterns/01-atoms/31-button-examples/*.yml')
      .pipe(clean())
      .pipe(concat('button-examples.yml', {newLine: ''}))
      .pipe(gulp.dest(config.patternLab.dir + '/source/_patterns/01-atoms/31-button-examples/'));
});

gulp.task('patternlab:generate:variables_as_json', function () {
  return gulp.src([config.patternLab.dir + '/source/scss/rpl.tier-*.scss', config.patternLab.dir + '/source/_patterns/01-atoms/05-buttons/rpla-button.mixin.scss'])
      .pipe(sass(
          {
            functions: export_sass('.')
          }
      ))
      .on('error', function (e) {
        console.log(e);
      });
});

gulp.task('patternlab:generate:variables_as_yml', function () {
  return gulp.src(config.patternLab.dir + '/source/_patterns/**/*.json')
      .pipe(clean()) // deletes the json files
      .pipe(jsonToYaml())
      .pipe(rename({extname: '.yml'}))
      .pipe(gulp.dest(config.patternLab.dir + '/source/_patterns'))
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
gulp.task('clean:svgs', function (callback) {
  del.sync([
    config.svgs.output
  ]);

  del.sync([
    config.svgs.inline_intermediate
  ]);

  callback();
});

/**
 * Removes the css files
 */
gulp.task('clean:styles', function (callback) {
  del.sync([
    config.styles.output
  ])

  del.sync([
    config.patternLab.publicCssDir
  ]);

  callback();
});

/**
 * Remove and rebuild the svg file
 */
gulp.task('build:svgs', function (callback) {
  return gulp.src(config.svgs.input)
      .pipe(tap(function (file, t) {
          return gulp.src(file.path)
            .pipe(rename({prefix: 'rpl-svg-'}))
            .pipe(svgmin())
            .pipe(svgstore({inlineSvg: true}))
            .pipe(cheerio(function ($, file) {
              $('symbol').addClass($('symbol').attr('id'));
            }, { decodeEntities: false }))
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
      .pipe(cheerio(function ($, file) {
        $('symbol').each(function (index, element) { $(this).addClass($(this).attr('id')) });
      }, { decodeEntities: false }))
      .pipe(gulp.dest(config.svgs.output));
});

/**
 * Generate a YML file so that PatternLab can display all the icons in our library
 */
gulp.task('patternlab:generate-icon-library-yml', function (callback) {
  fs.writeFileSync(config.patternLab.iconLibraryDataFile, "icon_library:\n");

  return gulp.src(config.svgs.input)
      .pipe(tap(function (file, t) {
        fs.appendFileSync(config.patternLab.iconLibraryDataFile, '  - ' + file.relative.substring(0, file.relative.length - 4) + "\n");
      }));
});

/**
 * Insert SVG file into PatternLab header
 */
gulp.task('patternlab:grab-svgs', function (callback) {
  return gulp.src(config.patternLab.metaDir + config.patternLab.headFilename)
      .pipe(cheerio(function ($, file) {
        var svgs = fs.readFileSync(config.svgs.output + config.svgs.output_filename, "utf8");
        $('#do-not-replace-see-gulpfile').find('svg').replaceWith(svgs);
      }, { decodeEntities: false }))
      .pipe(gulp.dest(config.patternLab.metaDir));
});

gulp.task('build:inline:svgs', function () {
  return gulp.src(config.svgs.inlined_svg)
    .pipe(cheerio(function ($, file) {
      $symbol_id = file.relative.replace(/\.[^/.]+$/, "");
      $('*').remove();
      $.root().append('.rpl-background-svg-' + $symbol_id + ' {');
      $.root().append("background-image: inline('" + $symbol_id + ".svg');");
      $.root().append('}');
    }, { decodeEntities: false }))
    .pipe(rename({extname: '.css'}))
    .pipe(entities('decode'))
    .pipe(gulp.dest(config.svgs.inline_intermediate));
});

gulp.task('build:inline:svg_css', ['build:inline:svgs'], function () {
  return gulp.src(config.svgs.inline_intermediate  + '/*.css')
      .pipe(base64('../../../' + config.svgs.inline_input))
      .pipe(clean())
      .pipe(concat('_background-svgs.autogenerated.scss'))
      .pipe(gulp.dest(config.styles.input + '/base'));
});

/**
 * Insert JS files into PatternLab footer
 */
gulp.task('patternlab:javascript', function () {
  return gulp.src(config.patternLab.metaDir + config.patternLab.footFilename)
      .pipe(replace(/<div id="do-not-replace-see-gulpfile" style="height: 0; width: 0; position: absolute; visibility: hidden">(.*?)<\/div>/g, function(match, p1, offset, string) {
        var output = '<div id="do-not-replace-see-gulpfile" style="height: 0; width: 0; position: absolute; visibility: hidden">';
        for (var full_path_idx in config.javascript.drupalDependencies) {
          var full_path = config.javascript.drupalDependencies[full_path_idx];
          var split_out = full_path.split('/');
          var file_name = split_out[split_out.length - 1];
          output += '<script src="../../js/core/' + file_name + '"></script>';
        }
        fs.readdirSync(config.javascript.jsDir + '/patterns').forEach(file => {
          output += '<script src="../../js/patterns/' + file + '"></script>';
        });

        output += '</div>';

        return output;
      }))
      .pipe(gulp.dest(config.patternLab.metaDir));
});

// .pipe(cheerio(function ($, file) {
//
//   var output = '';
//   for (var full_path_idx in config.javascript.drupalDependencies) {
//     full_path = config.javascript.drupalDependencies[full_path_idx];
//     var split_out = full_path.split('/');
//     var file_name = split_out[split_out.length - 1];
//     output += '<script src="../../js/' + file_name + '"></script>';
//   }
//   $('#do-not-replace-see-gulpfile').empty().append(output);
// }, { decodeEntities: false }))

/**
 * Task sequence to run when pattern files have changed.
 */
gulp.task('patterns-change', function (callback) {
  return runSequence('patternlab:generate', 'bs:reload', callback);
});

/**
 * Task sequence to run when SVG files have changed
 */
gulp.task('svgs-change', function (callback) {
  return runSequence('clean:svgs', 'build:svgs', 'build:inline:svg_css', 'patternlab:grab-svgs', 'patternlab:generate-icon-library-yml', callback);
});

/**
 * Task sequence to run when Sass files have changed.
 */
gulp.task('sass-change', function (callback) {
  return runSequence(['build:styles', 'lint:styles'], 'build:copy-css', 'bs:reload', callback);
});

gulp.task('javascript-change', function (callback) {
  return runSequence('build:javascript', 'bs:reload', callback);
});

gulp.task('bs:start', function() {
  if (config.browserSync.proxy.target) {
    browserSync.init({
      proxy: config.browserSync.proxy,
      open: config.browserSync.open,
      notify: false
    });
  } else {
    browserSync.init({
      server: config.browserSync.server,
      open: config.browserSync.open,
      notify: false
    });
  }
});


// Watch Files For Changes
gulp.task('watch', function(callback) {
  gulp.watch(config.styles.watchFiles, ['sass-change']);
  gulp.watch(config.patternLab.watchFiles, ['patterns-change']);
  gulp.watch(config.svgs.input, ['svgs-change']);
  gulp.watch(config.javascript.pattern_javascript, ['javascript-change']);
});

gulp.task('clean', ['clean:styles','clean:javascript', 'clean:svgs']);

// The build task called by Circle CI
gulp.task('build', function(callback) {
  return runSequence('svgs-change', 'sass-change', 'patternlab:generate:variables', 'build:javascript', 'patternlab:generate', 'bs:reload', callback);
});

// Default Task
gulp.task('default', function() {
  return runSequence(['bs:start', 'build'], 'watch');
});