'use strict';
var gutil = require('gulp-util');
var through = require('through2');
var chalk = require('chalk');
var prettyBytes = require('pretty-bytes');
var StreamCounter = require('stream-counter');
var objectAssign = require('object-assign');

var PLUGIN_NAME = 'gulp-warn-size';

var defaults = {
    pretty: true,
    errorOnFail: true,
    filter: /.*/
};

module.exports = function (opts) {
    
    if(typeof opts === 'object') {
        opts = objectAssign(defaults, opts);
    } else {
        opts = objectAssign(defaults, {
            limit: opts
        });
    }
    
    var limit = parseInt(opts.limit);
    if(isNaN(limit)) {
        throw new gutil.PluginError('gulp-warn-size', 'You must specify a (valid) file size limit in bytes');
    }

    if(typeof opts.filter === 'string') {
        opts.filter = new RegExp(opts.filter);
    } else if(!(opts.filter instanceof RegExp)) {
        throw new gutil.PluginError('gulp-warn-size', 'Filter option must be a regular expression');
    }
    
    function log(what, size, limit) {
        var fSize = opts.pretty ? prettyBytes(size) : (size + ' B');
        var fLimit = opts.pretty ? prettyBytes(limit) : (limit + ' B');
        var diff = Math.abs(limit - size);
        var fDiff = opts.pretty ? prettyBytes(diff) : (diff + ' B');
        
        var message =
            chalk.red('File size limit exceeded: ') + chalk.blue(what) + ' is ' + chalk.magenta(fSize) +
            ' which exceeds its limit of ' + chalk.magenta(fLimit) +
            ' by ' + chalk.red(fDiff) + ' ' + (opts.pretty ? chalk.magenta('(' +diff + ' B)') : '');
        gutil.log(message);
    }

    return through.obj(function (file, enc, cb) {
        if (file.isNull()) {
            cb(null, file);
            return;
        }

        var finish = function (err, size) {
            if (err) {
                cb(new gutil.PluginError(PLUGIN_NAME, err));
                return;
            }
            
            if(opts.filter.test(file.relative) && limit >= 0 && size > limit) {
                log(file.relative, size, limit);

                if(opts.errorOnFail) {
                    cb(new gutil.PluginError(PLUGIN_NAME, 'Failing due to file limit being exceeded.'));
                }
            } else {
                cb(null, file);
            }
        };

        if (file.isStream()) {
            file.contents.pipe(new StreamCounter())
                .on('error', function(err) {
                    finish(err);
                })
                .on('finish', function () {
                    finish(null, this.bytes);
                });
        } else {
            finish(null, file.contents.length);
        }
    });
};