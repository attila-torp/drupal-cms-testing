<?php

namespace Drupal\labl_blocks\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\labl_blocks\Storybook\StoryDiscovery;

/**
 * Derives block plugins for each Storybook organism.
 */
class StorybookBlockDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $discovery = new StoryDiscovery();
    foreach ($discovery->discover() as $id => $story) {
      $definition = $base_plugin_definition;
      $definition['admin_label'] = $story['label'];
      $definition['category'] = $base_plugin_definition['category'] ?? 'Labl Components';
      $definition['story'] = $story;
      $this->derivatives[$id] = $definition;
    }

    return $this->derivatives;
  }

}
