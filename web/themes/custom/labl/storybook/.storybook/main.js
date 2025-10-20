/**
 * Storybook configuration for the LABL standalone instance.
 *
 * This configuration is purposefully isolated from the Drupal build so that
 * the design system can be developed and tested independently.
 */

const config = {
  stories: ['../components/**/*.stories.@(js|ts)'],
  addons: ['@storybook/addon-essentials'],
  framework: {
    name: '@storybook/html-webpack5',
    options: {},
  },
  webpackFinal: async (config) => {
    config.module.rules.push(
      {
        test: /\.twig$/,
        use: [
          {
            loader: 'twig-loader',
            options: {
              twigOptions: {
                allowInlineIncludes: true,
              },
            },
          },
        ],
      },
      {
        test: /\.ya?ml$/,
        type: 'json',
        use: 'yaml-loader',
      }
    );

    return config;
  },
};

export default config;
