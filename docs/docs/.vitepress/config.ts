import {defineConfig} from 'vitepress'

export default defineConfig({
  title: 'Instant Analytics Plugin',
  description: 'Documentation for the Instant Analytics plugin',
  base: '/docs/instant-analytics/',
  lang: 'en-US',
  head: [
    ['meta', {content: 'https://github.com/nystudio107', property: 'og:see_also',}],
    ['meta', {content: 'https://twitter.com/nystudio107', property: 'og:see_also',}],
    ['meta', {content: 'https://youtube.com/nystudio107', property: 'og:see_also',}],
    ['meta', {content: 'https://www.facebook.com/newyorkstudio107', property: 'og:see_also',}],
  ],
  themeConfig: {
    socialLinks: [
      {icon: 'github', link: 'https://github.com/nystudio107'},
      {icon: 'twitter', link: 'https://twitter.com/nystudio107'},
    ],
    logo: '/img/plugin-logo.svg',
    editLink: {
      pattern: 'https://github.com/nystudio107/craft-instantanalytics/edit/develop/docs/docs/:path',
      text: 'Edit this page on GitHub'
    },
    algolia: {
      appId: '0MW80XT8MA',
      apiKey: '8ce26433f6d3f3a432b0d7d96c786b3c',
      indexName: 'instant-analytics'
    },
    lastUpdatedText: 'Last Updated',
    sidebar: [
      {
        text: 'Topics',
        items: [
          {text: 'Instant Analytics Plugin', link: '/'},
          {text: 'Instant Analytics Overview', link: '/overview.html'},
          {text: 'Use Cases', link: '/use-cases.html'},
          {text: 'Configuring Instant Analytics', link: '/configuring.html'},
          {text: 'Using Instant Analytics', link: '/using.html'},
        ],
      }
    ],
    nav: [
      {text: 'Home', link: 'https://nystudio107.com/plugins/instant-analytics'},
      {text: 'Store', link: 'https://plugins.craftcms.com/instant-analytics'},
      {text: 'Changelog', link: 'https://nystudio107.com/plugins/instant-analytics/changelog'},
      {text: 'Issues', link: 'https://github.com/nystudio107/craft-instantanalytics/issues'},
    ],
  },
});
