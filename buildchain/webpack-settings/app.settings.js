// app.settings.js

// node modules
require('dotenv').config();

// settings
module.exports = {
    alias: {
    },
    copyright: '©2020 nystudio107.com',
    entry: {
        'instantanalytics': '../src/assetbundles/instantanalytics/src/js/InstantAnalytics.js',
        'welcome': '../src/assetbundles/instantanalytics/src/js/Welcome.js',
    },
    extensions: ['.ts', '.js', '.vue', '.json'],
    name: 'instantanalytics',
    paths: {
        dist: '../../src/assetbundles/instantanalytics/dist/',
    },
    urls: {
        publicPath: () => process.env.PUBLIC_PATH || '',
    },
};
