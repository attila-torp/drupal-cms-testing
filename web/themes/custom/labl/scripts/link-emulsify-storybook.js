#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

const themeRoot = path.resolve(__dirname, '..');
const emulsifyRoot = path.resolve(themeRoot, '../contrib/emulsify');
const sourceComponents = path.join(themeRoot, 'components');
const targetDirectory = path.join(emulsifyRoot, 'components', '_project');
const linkLocation = path.join(targetDirectory, 'labl');

if (!fs.existsSync(emulsifyRoot)) {
  console.error('Emulsify theme not found at %s. Run `composer require drupal/emulsify` and install its npm dependencies first.', emulsifyRoot);
  process.exit(1);
}

if (!fs.existsSync(sourceComponents)) {
  console.error('No components directory found at %s. Nothing to link for Storybook.', sourceComponents);
  process.exit(0);
}

fs.mkdirSync(targetDirectory, { recursive: true });

try {
  if (fs.existsSync(linkLocation)) {
    const existing = fs.lstatSync(linkLocation);
    if (!existing.isSymbolicLink()) {
      console.error('Cannot create Storybook link because %s already exists and is not a symlink.', linkLocation);
      process.exit(1);
    }

    const currentTarget = fs.readlinkSync(linkLocation);
    if (path.resolve(targetDirectory, currentTarget) === sourceComponents) {
      console.info('Storybook link already points to %s', sourceComponents);
      process.exit(0);
    }

    fs.unlinkSync(linkLocation);
  }

  const relativeTarget = path.relative(targetDirectory, sourceComponents) || '.';
  fs.symlinkSync(relativeTarget, linkLocation, 'junction');
  console.info('Linked %s to %s for Storybook previews.', linkLocation, sourceComponents);
} catch (error) {
  console.error('Failed to link LABL components into Emulsify Storybook: %s', error.message);
  process.exit(1);
}
