<?php

namespace Drupal\labl_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides 'Hero Header 1' block.
 *
 * @Block(
 *   id = "hero_header_1",
 *   admin_label = @Translation("Hero Header 1"),
 *   category = @Translation("Labl Components")
 * )
 */
class HeroHeader1Block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'componentTheme' => 'light',
        'mediaLeft' => FALSE,
        'subscribeForm' => FALSE,
        'contentBorder' => FALSE,
        'ctas' => [],
        'heroHeading' => '',
        'heroText' => '',
        'image' => '',
        'video' => '',
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['componentTheme'] = [
      '#type' => 'select',
      '#title' => $this->t('Component Theme'),
      '#options' => ['light' => 'Light', 'dark' => 'Dark'],
      '#default_value' => $config['componentTheme'],
    ];
    $form['mediaLeft'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Media on left'),
      '#default_value' => $config['mediaLeft'],
    ];
    $form['subscribeForm'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Subscribe Form'),
      '#default_value' => $config['subscribeForm'],
    ];
    $form['contentBorder'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Content border'),
      '#default_value' => $config['contentBorder'],
    ];
    $form['heroHeading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hero heading'),
      '#default_value' => $config['heroHeading'],
    ];
    $form['heroText'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Hero text'),
      '#default_value' => $config['heroText'],
    ];
    $form['image'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Image URL'),
      '#default_value' => $config['image'],
    ];
    $form['video'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Video URL or embed'),
      '#default_value' => $config['video'],
    ];
    // For CTAs, you might provide a textarea JSON input or repeated fields (for simplicity, skip now)

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['componentTheme'] = $values['componentTheme'];
    $this->configuration['mediaLeft'] = $values['mediaLeft'];
    $this->configuration['subscribeForm'] = $values['subscribeForm'];
    $this->configuration['contentBorder'] = $values['contentBorder'];
    $this->configuration['heroHeading'] = $values['heroHeading'];
    $this->configuration['heroText'] = $values['heroText'];
    $this->configuration['image'] = $values['image'];
    $this->configuration['video'] = $values['video'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $cfg = $this->getConfiguration();

    $loader = \Drupal::service('twig.loader.filesystem');
    $paths = $loader->getPaths('storybook');
    \Drupal::logger('storybook')->notice('Namespace @storybook paths at block build: @paths', [
      '@paths' => implode(', ', $paths),
    ]);

    return [
      '#theme' => 'hero_header_1',
      '#componentTheme' => $cfg['componentTheme'],
      '#mediaLeft' => $cfg['mediaLeft'],
      '#subscribeForm' => $cfg['subscribeForm'],
      '#contentBorder' => $cfg['contentBorder'],
      '#heroHeading' => $cfg['heroHeading'],
      '#heroText' => $cfg['heroText'],
      '#image' => $cfg['image'],
      '#video' => $cfg['video'],
      // '#ctas' => ... (you may parse CTAs config earlier)
      '#attached' => [
        'library' => [
          'labl_blocks/hero-header-1',  // if you have CSS/JS
        ],
      ],
    ];
  }

}
