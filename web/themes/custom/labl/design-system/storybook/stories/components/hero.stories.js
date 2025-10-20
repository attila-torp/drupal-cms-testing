import heroTemplate from '@labl/02-molecules/hero/hero.twig';
import buttonTemplate from '@emulsify/01-atoms/button/button.twig';

export default {
  title: 'Molecules/Hero',
  args: {
    title: 'Build compelling experiences faster',
    body: 'The Labl theme is powered by the Emulsify component library. Mix and match components to craft delightful user interfaces.',
  },
};

const Template = (args) =>
  heroTemplate({
    ...args,
    cta: buttonTemplate({ text: 'Explore components' }),
  });

export const Default = Template.bind({});
