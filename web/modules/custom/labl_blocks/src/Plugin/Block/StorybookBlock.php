<?php

namespace Drupal\labl_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a generic Storybook-backed block.
 *
 * @Block(
 *   id = "storybook_component",
 *   admin_label = @Translation("Storybook component"),
 *   category = @Translation("Labl Components"),
 *   deriver = "Drupal\labl_blocks\Plugin\Derivative\StorybookBlockDeriver"
 * )
 */
class StorybookBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $defaults = [];
    foreach ($this->getFields() as $field) {
      $defaults[$field['name']] = $field['default'];
    }

    return $defaults + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    foreach ($this->getFields() as $field) {
      $name = $field['name'];
      $value = $config[$name] ?? $field['default'];
      $form[$name] = $this->buildElement($field, $value);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    foreach ($this->getFields() as $field) {
      $name = $field['name'];
      $value = $form_state->getValue($name);
      $this->configuration[$name] = $this->normalizeValue($field, $value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $definition = $this->getStoryDefinition();

    $build = [
      '#theme' => $definition['theme'],
    ];

    foreach ($this->getFields() as $field) {
      $name = $field['name'];
      $build['#' . $name] = $config[$name] ?? $field['default'];
    }

    $build['#attached']['library'][] = 'labl_blocks/storybook-components';

    return $build;
  }

  /**
   * Retrieves the story definition for the current derivative.
   */
  protected function getStoryDefinition(): array {
    return $this->pluginDefinition['story'] ?? [];
  }

  /**
   * Retrieves the story fields for the current derivative.
   */
  protected function getFields(): array {
    $definition = $this->getStoryDefinition();
    return $definition['fields'] ?? [];
  }

  /**
   * Builds an individual form element.
   */
  protected function buildElement(array $field, $value): array {
    $element = [
      '#title' => $this->t($this->humanize($field['name'])),
      '#default_value' => $value,
    ];

    switch ($field['element']) {
      case 'checkbox':
        $element['#type'] = 'checkbox';
        $element['#default_value'] = !empty($value);
        break;

      case 'number':
        $element['#type'] = 'number';
        break;

      case 'textarea':
        $element['#type'] = 'textarea';
        if ($field['store_json']) {
          $element['#default_value'] = $value ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '';
          $element['#description'] = $this->t('Provide a JSON value.');
        }
        break;

      case 'select':
        $element['#type'] = 'select';
        $element['#options'] = array_combine($field['options'], $field['options']);
        if ($field['multiple']) {
          $element['#multiple'] = TRUE;
          if (is_string($value)) {
            $decoded = json_decode($value, TRUE);
            $value = is_array($decoded) ? $decoded : ($value === '' ? [] : [$value]);
          }
        }
        else {
          $element['#empty_option'] = $this->t('- Select -');
        }
        $element['#default_value'] = $value;
        break;

      case 'checkboxes':
        $element['#type'] = 'checkboxes';
        $element['#options'] = array_combine($field['options'], $field['options']);
        $selected = [];
        if (is_array($value)) {
          foreach ($value as $optionKey => $optionValue) {
            if (is_scalar($optionValue)) {
              $selected[(string) $optionValue] = TRUE;
            }
            elseif (is_scalar($optionKey) && $optionValue) {
              $selected[(string) $optionKey] = TRUE;
            }
          }
        }
        $element['#default_value'] = array_keys($selected);
        break;

      case 'radios':
        $element['#type'] = 'radios';
        $element['#options'] = array_combine($field['options'], $field['options']);
        break;

      case 'textfield':
      default:
        $element['#type'] = 'textfield';
        break;
    }

    $type = $element['#type'] ?? NULL;
    if (in_array($type, ['textfield', 'textarea'], TRUE) && empty($field['store_json'])) {
      if (!is_string($element['#default_value'])) {
        if (is_scalar($element['#default_value'])) {
          $element['#default_value'] = (string) $element['#default_value'];
        }
        else {
          $element['#default_value'] = '';
        }
      }
    }

    if ($field['element'] === 'textarea' && empty($element['#rows'])) {
      $element['#rows'] = 5;
    }

    return $element;
  }

  /**
   * Normalizes the submitted value based on field metadata.
   */
  protected function normalizeValue(array $field, $value) {
    switch ($field['element']) {
      case 'checkbox':
        return (bool) $value;

      case 'number':
        if ($value === '' || $value === NULL) {
          return NULL;
        }
        return $field['value_type'] === 'integer' ? (int) $value : (float) $value;

      case 'textarea':
        if ($field['store_json']) {
          if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, TRUE);
            return is_array($decoded) ? $decoded : $field['default'];
          }
          return [];
        }
        return $value;

      case 'select':
        if ($field['multiple']) {
          if (is_array($value)) {
            return array_values(array_filter($value, static fn($v) => $v !== '' && $v !== NULL));
          }
          return [];
        }
        return $value === '' ? NULL : $value;

      case 'checkboxes':
        if (!is_array($value)) {
          return [];
        }
        $selected = [];
        foreach ($value as $optionKey => $optionValue) {
          if ($optionValue === 0 || $optionValue === '' || $optionValue === NULL) {
            continue;
          }
          if (is_string($optionKey)) {
            $selected[] = $optionKey;
          }
          elseif (is_scalar($optionValue)) {
            $selected[] = (string) $optionValue;
          }
        }
        return array_values(array_unique($selected));

      case 'radios':
        return $value === '' ? NULL : $value;

      default:
        return $value;
    }
  }

  /**
   * Converts a machine key into a user-friendly label.
   */
  protected function humanize(string $key): string {
    $key = preg_replace('/[_-]+/', ' ', $key);
    return ucwords(trim((string) $key));
  }

}
