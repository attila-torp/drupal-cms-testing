# LABL standalone Storybook

This directory hosts an isolated Storybook instance for the LABL theme. It can
run independently from the Drupal project so front-end developers can build and
review components without a local CMS.

## Getting started

```
npm install
npm run storybook
```

> **Note**
> Install and run commands must be executed from this `storybook/` directory to
> keep the Storybook dependency tree separate from the Drupal build.

## YAML-driven block configuration

Each block-level component reads a YAML configuration file in
`config/blocks/*.yml`. The file describes the fields that Drupal would expose to
Twig when the block is rendered.

```yaml
label: Example Content Block
fields:
  title:
    type: text
    label: Title
    default: "Ready for launch"
```

Story files can import these YAML definitions directly, which keeps the mock data
in sync with Drupal field configuration.

```js
import blockConfig from '../../config/blocks/example-block.yml';

const defaults = Object.fromEntries(
  Object.entries(blockConfig.fields).map(([key, value]) => [key, value.default])
);
```

The accompanying Twig template receives those values so that the Storybook view
matches the eventual Drupal block output.
