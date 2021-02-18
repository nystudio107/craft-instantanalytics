// app.settings.js

// node modules
require('dotenv').config();
const path = require('path');

// settings
module.exports = {
    alias: {
        '@css': path.resolve('../src/assetbundles/instantanalytics/src/css'),
        '@img': path.resolve('../src/assetbundles/instantanalytics/src/img'),
        '@js': path.resolve('../src/assetbundles/instantanalytics/src/js'),
        '@vue': path.resolve('../src/assetbundles/instantanalytics/src/vue'),
    },
    copyright: '©2020 nystudio107.com',
    entry: {
        'instantanalytics': '@js/InstantAnalytics.js',
        'welcome': '@js/Welcome.js',
    },
    extensions: ['.ts', '.js', '.vue', '.json'],
    name: 'instantanalytics',
    paths: {
        dist: path.resolve('../../src/assetbundles/instantanalytics/dist/'),
    },
    urls: {
        publicPath: () => process.env.PUBLIC_PATH || '',
    },
};
