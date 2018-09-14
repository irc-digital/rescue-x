var args = require('minimist')(process.argv.slice(2));
var default_environment = 'localhost:3000';

// Environments that are being compared
if (!args.env) {
    args.env = default_environment;
}

var config = {
    // The viewports to test each component at
    "viewports": [
        {
            "label": "mobile",
            "width": 320,
            "height": 1200
        },
        {
            "label": "phablet",
            "width": 640,
            "height": 1200
        },
        {
            "label": "tablet",
            "width": 768,
            "height": 768
        },
        {
            "label": "laptop",
            "width": 1024,
            "height": 1024
        },
        {
            "label": "desktop",
            "width": 1208,
            "height": 1208
        }
    ],


    // Runs a CLI and Browser based test report
    report: ['CLI', 'browser'],
    cliExitOnFail: false,
    debug: false,
    scenarios: [],
    engine: "puppeteer",
    id: "rescue-x",
    paths: {
        "bitmaps_reference": "backstop_data/bitmaps_reference",
        "bitmaps_test": "backstop_data/bitmaps_test",
        "engine_scripts": "backstop_data/engine_scripts",
        "html_report": "backstop_data/html_report",
        "ci_report": "backstop_data/ci_report"
    },
    "engineOptions": {
        "args": ["--no-sandbox"]
    }
};

var dir = require('node-dir');

// Build a list of component twig files
var basePath = 'dist/source/_patterns/05-components';
var files = dir.files(basePath, {sync:true});

var twigFiles = files.filter(function(file){
    return file.indexOf('twig') !== -1 || file.indexOf('~') !== -1;
});

// Add config paths for each component
var arrayLength = twigFiles.length;
for (var i = 0; i < arrayLength; i++) {
    // Strip directories from file paths
    var fileName = twigFiles[i].replace(/^.*[\\\/]/, '');
    // Variation formatting
    fileName = fileName.replace("~", "-");
    // Remove file extensions
    var pattern = fileName.substring(0, fileName.lastIndexOf("."));
    config.scenarios.push({
        'label': pattern,
        'url': 'http://' + args.env + '/?p=components-' + pattern,
        'referenceUrl': 'http://' + args.env + '/?p=components-' + pattern,
        'readyEvent': null,
        'delay': 500,
        'misMatchThreshold' : .1,
        'onReadyScript' : null,
        'onBeforeScript' : null,
        'selectors': ["body"]
    });
}

module.exports = config;