module.exports = {
    title: 'Instant Analytics Plugin Documentation',
    description: 'Documentation for the Instant Analytics plugin',
    base: '/docs/instant-analytics/',
    lang: 'en-US',
    head: [
        ['meta', { content: 'https://github.com/nystudio107', property: 'og:see_also', }],
        ['meta', { content: 'https://twitter.com/nystudio107', property: 'og:see_also', }],
        ['meta', { content: 'https://youtube.com/nystudio107', property: 'og:see_also', }],
        ['meta', { content: 'https://www.facebook.com/newyorkstudio107', property: 'og:see_also', }],
    ],
    themeConfig: {
        repo: 'nystudio107/craft-instantanalytics',
        docsDir: 'docs/docs',
        docsBranch: 'develop',
        algolia: {
            apiKey: '',
            indexName: 'instantanalytics'
        },
        editLinks: true,
        editLinkText: 'Edit this page on GitHub',
        lastUpdated: 'Last Updated',
        sidebar: [
            { text: 'Instant Analytics Plugin', link: '/' },
            { text: 'Instant Analytics Overview', link: '/overview.html' },
            { text: 'Use Cases', link: '/use-cases.html' },
            { text: 'Configuring Instant Analytics', link: '/configuring.html' },
            { text: 'Using Instant Analytics', link: '/using.html' },
        ],
    },
};
