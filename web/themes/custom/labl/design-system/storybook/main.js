const path = require('path');

module.exports = {
  stories: ['../stories/**/*.stories.@(js|mdx|ts)'],
  addons: [
    '@storybook/addon-essentials',
    '@storybook/addon-a11y',
    '@storybook/addon-interactions',
    '@emulsify/storybook-addon-twig',
  ],
  framework: {
    name: '@storybook/html-webpack5',
    options: {},
  },
  docs: {
    autodocs: 'tag',
  },
  webpackFinal: async (config) => {
    const projectRoot = path.resolve(__dirname, '../../../../../..');

    config.resolve.alias = {
      ...(config.resolve.alias || {}),
      '@emulsify': path.resolve(projectRoot, 'web/themes/custom/emulsify/components'),
      '@labl': path.resolve(projectRoot, 'web/themes/custom/labl/components'),
    };

    return config;
  },
};
