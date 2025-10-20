# LABL theme

The LABL theme reuses the tooling that ships with the Emulsify base theme. Once
Composer installs `drupal/emulsify`, you can preview LABL components inside the
same Storybook workspace instead of maintaining a separate configuration.

## Prerequisites

1. Install PHP dependencies so the Emulsify theme exists at
   `web/themes/contrib/emulsify`:

   ```bash
   composer update drupal/emulsify
   ```

2. Install the Node dependencies that Emulsify ships with:

   ```bash
   cd web/themes/contrib/emulsify
   npm install
   ```

## Running Storybook for LABL components

The LABL `npm run storybook` script links the theme's `components/` directory
into Emulsify's pattern library so Storybook discovers it alongside the
upstream components.

```bash
cd web/themes/custom/labl
npm run storybook
```

The command will:

1. Verify that `web/themes/contrib/emulsify` exists and has its dependencies.
2. Symlink `web/themes/custom/labl/components` to
   `web/themes/contrib/emulsify/components/_project/labl`.
3. Launch Emulsify's Storybook instance (`npm run storybook --prefix
   ../contrib/emulsify`).

## Example: Example Content Block

`components/example-block` contains:

- `example-block.twig` – the Twig template Drupal will render.
- `example-block.yml` – field metadata and default values that Storybook exposes
  as controls.
- `example-block.stories.js` – a Storybook story that feeds the Twig template
  with the YAML defaults.
- `example-block.twig` accepts `body_paragraphs` so Drupal paragraph items or
  other multi-value fields can render each paragraph with proper markup.

When Storybook runs, you will find this component under **Blocks → Example
Content Block**. The same Twig template can be used inside Drupal by copying it
into `templates/block` with the appropriate suggestion or by including it from a
preprocess function.

## Using the Twig namespace in Drupal

`labl.theme` registers the Twig namespace `@labl`, allowing you to include
components from any theme template:

```twig
{% include '@labl/example-block/example-block.twig' with {
  title: 'Hello from Drupal',
  body: '<p>Rendered inside Drupal.</p>',
  link: { title: 'Learn more', url: path('entity.node.canonical', { node: 1 }) },
  highlights: [
    { label: 'Audience', value: 'Content editors' },
    { label: 'Status', value: 'Published' },
  ],
} %}
```

Keep the YAML and Twig in sync with the Drupal block fields to ensure Storybook
remains a reliable preview of the live component.
