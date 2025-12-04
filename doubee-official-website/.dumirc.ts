import { defineConfig } from 'dumi';
import { SiteThemeConfig } from 'dumi-theme-lobehub';

const themeConfig: SiteThemeConfig = {
  title: '豆付',
  name: 'Doubee Pay',
  actions: [
    {
      text: '快速上手',
      link: '/register',
    },
    {
      text: '即刻体验',
      link: '/register',
      type: 'primary',
    },
  ],
  prefersColor: {
    default: 'dark',
    switch: false,
  },
  socialLinks: {
    discord: 'https://discord.gg/',
    github: 'https://github.com/wechatpay-apiv3/wechatpay-java',
  },
  metadata: {
    description: '一站式聚合支付平台，支持扫码收款、小额支付、钱包账户、API 集成等功能。',
    icons: {
      icon: '/favicon.ico',
      apple: '/favicon.ico',
      shortcut: '/favicon.ico',
    },
  },
  footerConfig: {
    bottom: '成都豆付科技有限公司(Chengdu Doubee Technology Co., Ltd.) 版权所有 | <a href="https://beian.miit.gov.cn" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: none;">ICP备案号: 蜀ICP备2025174119号-1</a>',
    columns: [
      {
        title: '产品',
        items: [
          {
            title: '豆管家',
            url: 'https://douguanjia.com',
            openExternal: true,
          },
        ],
      },
      {
        title: '功能',
        items: [],
      },
      {
        title: '资源',
        items: [
          {
            title: '快速上手',
            url: '/',
          },
          {
            title: 'API文档',
            url: '/',
          },
          {
            title: '更新日志',
            url: '/',
          },
        ],
      },
      {
        title: '社区',
        items: [
          {
            title: 'GitHub社区',
            url: 'https://github.com/',
            openExternal: true,
          },
          {
            title: 'Discord社区',
            url: 'https://github.com/',
            openExternal: true,
          },
        ],
      },
      {
        title: '关于',
        items: [
          {
            title: '常见问题',
            url: '/',
          },
          {
            title: '联系我们',
            url: '/',
          },
        ],
      },
    ],
  },
}

export default defineConfig({
  mfsu: {
    esbuild: true,
  },
  jsMinifier: 'esbuild',
  cssMinifier: 'esbuild',
  headScripts: [
    {
      content: `
        (function() {
          // 强制设置主题存储为深色模式
          try {
            const storeKey = 'LOBE_DOC_STORE';
            const stored = localStorage.getItem(storeKey);
            let storeData = { themeMode: 'dark' };
            if (stored) {
              try {
                storeData = JSON.parse(stored);
                storeData.state = storeData.state || {};
                storeData.state.themeMode = 'dark';
              } catch (e) {
                storeData = { state: { themeMode: 'dark' } };
              }
            } else {
              storeData = { state: { themeMode: 'dark' } };
            }
            localStorage.setItem(storeKey, JSON.stringify(storeData));
          } catch (e) {}
          
          // 设置 HTML 属性
          const html = document.documentElement;
          html.setAttribute('data-prefers-color', 'dark');
          html.style.colorScheme = 'dark';
          
          // 阻止系统主题检测，强制覆盖
          const originalMatchMedia = window.matchMedia;
          window.matchMedia = function(query) {
            if (query === '(prefers-color-scheme: light)' || query === '(prefers-color-scheme: dark)') {
              return {
                matches: query === '(prefers-color-scheme: dark)',
                media: query,
                onchange: null,
                addListener: function() {},
                removeListener: function() {},
                addEventListener: function() {},
                removeEventListener: function() {},
                dispatchEvent: function() { return false; }
              };
            }
            return originalMatchMedia.call(window, query);
          };
        })();
      `,
    },
  ],
  themeConfig: {
    ...themeConfig,
  },
});
