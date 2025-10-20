# Labl design system

The Labl theme uses the Emulsify component library as the foundation for its Drupal theme and front-end design system. Storybook is configured to render the Twig components that ship with the base `emulsify` theme as well as any local component overrides that live in `web/themes/custom/labl/components`.

## Getting started

```bash
cd web/themes/custom/labl/design-system
npm install
npm run develop
```

The `develop` script boots Storybook at http://localhost:6006 with hot reloading.

To create a static build of the design system:

```bash
npm run build
```

The static Storybook will be emitted into `web/themes/custom/labl/design-system/dist`.

## Component locations

- **Base components:** `web/themes/custom/emulsify/components`
- **Custom components:** `web/themes/custom/labl/components`

Custom components automatically override the base implementation when a Twig template with the same path and filename is added to `web/themes/custom/labl/components`.
