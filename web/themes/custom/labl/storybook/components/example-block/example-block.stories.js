import blockConfig from '../../config/blocks/example-block.yml';
import template from './example-block.twig';

const { fields, label, summary } = blockConfig;

const fieldDefaults = Object.entries(fields).reduce((accumulator, [key, value]) => {
  accumulator[key] = value.default;
  return accumulator;
}, {});

const render = (args) => template({ ...fieldDefaults, ...args });

export default {
  title: `Blocks/${label}`,
  parameters: {
    docs: {
      description: {
        story: summary,
      },
    },
  },
  render,
  args: fieldDefaults,
  argTypes: Object.entries(fields).reduce((accumulator, [key, value]) => {
    accumulator[key] = {
      name: value.label,
      description: `${value.type} field defined in example-block.yml`,
      control: value.type === 'list' ? 'object' : 'text',
    };
    return accumulator;
  }, {}),
};

export const Default = {
  name: 'Default configuration',
};
