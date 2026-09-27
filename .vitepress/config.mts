import { defineConfig } from 'vitepress'

export default defineConfig({
  srcDir: 'docs',
  base: '/Laraprep/',
  title: 'Laraprep',
  description: 'Safe backend setup scripts and codemods for Laravel projects.',
  cleanUrls: true,
  lastUpdated: true,

  themeConfig: {
    nav: [],
    sidebar: [
      {
        text: 'Documentation',
        items: [
          { text: 'Overview', link: '/' },
          { text: 'Installation', link: '/installation' },
          {
            text: 'Commands',
            items: [
              { text: 'Fortify Backend', link: '/fortify-backend' },
              { text: 'Options Reference', link: '/options' },
            ],
          },
          { text: 'Behavior And Safety', link: '/behavior-and-safety' },
          { text: 'Workbench', link: '/workbench' },
          { text: 'Development', link: '/development' },
          { text: 'Known Limitations', link: '/known-limitations' },
        ],
      },
    ],
    socialLinks: [
      { icon: 'github', link: 'https://github.com/maxiviper117/laraprep' },
    ],
    search: { provider: 'local' },
    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright © 2026 David',
    },
  },
})
