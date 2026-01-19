<?php

namespace Drupal\labl_gutenberg\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides 'Hero Animated' block.
 *
 * @Block(
 *   id = "hero_animated",
 *   admin_label = @Translation("Hero Animated"),
 *   category = @Translation("Labl Components")
 * )
 */
class HeroAnimatedBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'heading' => '',
      'description' => '',
      'hasCta' => FALSE,
      'ctaLabel' => '',
      'ctaUrl' => '',
      'ctaType' => 'primary',
      'theme' => 'theme-1',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Heading'),
      '#default_value' => $config['heading'],
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $config['description'],
    ];
    $form['hasCta'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display CTA'),
      '#default_value' => $config['hasCta'],
    ];
    $form['ctaLabel'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA Label'),
      '#default_value' => $config['ctaLabel'],
      '#states' => [
        'visible' => [
          ':input[name="settings[hasCta]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['ctaUrl'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CTA URL'),
      '#default_value' => $config['ctaUrl'],
      '#states' => [
        'visible' => [
          ':input[name="settings[hasCta]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['ctaType'] = [
      '#type' => 'select',
      '#title' => $this->t('CTA Style'),
      '#options' => [
        'primary' => $this->t('Primary'),
        'secondary' => $this->t('Secondary'),
        'outline' => $this->t('Outline'),
      ],
      '#default_value' => $config['ctaType'],
      '#states' => [
        'visible' => [
          ':input[name="settings[hasCta]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#options' => [
        'theme-1' => $this->t('Light'),
        'theme-2' => $this->t('Dark'),
      ],
      '#default_value' => $config['theme'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();

    $this->configuration['heading'] = $values['heading'];
    $this->configuration['description'] = $values['description'];
    $this->configuration['hasCta'] = $values['hasCta'];
    $this->configuration['ctaLabel'] = $values['ctaLabel'];
    $this->configuration['ctaUrl'] = $values['ctaUrl'];
    $this->configuration['ctaType'] = $values['ctaType'];
    $this->configuration['theme'] = $values['theme'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $cfg = $this->getConfiguration();

    return [
      '#theme' => 'hero_animated',
      '#heading' => $cfg['heading'],
      '#description' => $cfg['description'],
      '#hasCta' => $cfg['hasCta'],
      '#ctaLabel' => $cfg['ctaLabel'],
      '#ctaUrl' => $cfg['ctaUrl'],
      '#ctaType' => $cfg['ctaType'],
      '#darkTheme' => $cfg['theme'] === 'theme-2',
    ];
  }

}
