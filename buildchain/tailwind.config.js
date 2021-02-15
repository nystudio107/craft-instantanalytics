// module exports
module.exports = {
  purge: {
    content: [
      '../src/templates/**/*.{twig,html}',
      '../src/assetbundles/instantanalytics/src/vue/**/*.{vue,html}',
    ],
    layers: [
      'base',
      'components',
      'utilities',
    ],
    mode: 'layers',
    options: {
      whitelist: [
        '../src/assetbundles/instantanalytics/src/css/components/*.css',
      ],
    }
  },
  theme: {
  },
  corePlugins: {},
  plugins: [],
};
