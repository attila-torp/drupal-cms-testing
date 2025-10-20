import buttonTemplate from '@emulsify/01-atoms/button/button.twig';

export default {
  title: 'Atoms/Button',
  args: {
    text: 'Call to action',
    variant: 'primary',
  },
  argTypes: {
    text: { control: 'text' },
    variant: {
      control: 'select',
      options: ['primary'],
    },
  },
};

const Template = (args) => buttonTemplate(args);

export const Primary = Template.bind({});
Primary.args = {
  text: 'Get started',
};
