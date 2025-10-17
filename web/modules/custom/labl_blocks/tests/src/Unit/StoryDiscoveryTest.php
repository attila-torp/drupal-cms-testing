<?php

namespace Drupal\Tests\labl_blocks\Unit;

use Drupal\labl_blocks\Storybook\StoryDiscovery;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\labl_blocks\Storybook\StoryDiscovery
 */
class StoryDiscoveryTest extends TestCase {

  /**
   * Provides access to protected methods for testing.
   */
  protected function getTestDiscovery(): StoryDiscoveryTestProxy {
    return new StoryDiscoveryTestProxy();
  }

  /**
   * @covers ::buildFieldDefinition
   */
  public function testSequentialOptionsBecomeKeyValuePairs(): void {
    $discovery = $this->getTestDiscovery();
    $definition = $discovery->publicBuildFieldDefinition('variant', 'primary', [
      'options' => ['primary', 'secondary'],
    ]);

    $this->assertSame([
      'primary' => 'primary',
      'secondary' => 'secondary',
    ], $definition['options']);
  }

  /**
   * @covers ::buildFieldDefinition
   */
  public function testAssociativeOptionsPreserveKeys(): void {
    $discovery = $this->getTestDiscovery();
    $definition = $discovery->publicBuildFieldDefinition('variant', 'primary', [
      'options' => [
        'primary' => 'Primary',
        'secondary' => 'Secondary',
      ],
    ]);

    $this->assertSame([
      'primary' => 'Primary',
      'secondary' => 'Secondary',
    ], $definition['options']);
  }

  /**
   * @covers ::buildFieldDefinition
   */
  public function testAssociativeOptionsWithLabelKeysAreInverted(): void {
    $discovery = $this->getTestDiscovery();
    $definition = $discovery->publicBuildFieldDefinition('variant', 'primary', [
      'options' => [
        'Primary' => 'primary',
        'Secondary' => 'secondary',
      ],
    ]);

    $this->assertSame([
      'primary' => 'Primary',
      'secondary' => 'Secondary',
    ], $definition['options']);
  }

  /**
   * @covers ::buildFieldDefinition
   */
  public function testOptionsArrayWithValueLabelPairs(): void {
    $discovery = $this->getTestDiscovery();
    $definition = $discovery->publicBuildFieldDefinition('variant', 'primary', [
      'options' => [
        ['value' => 'primary', 'label' => 'Primary'],
        ['value' => 'secondary', 'label' => 'Secondary'],
      ],
    ]);

    $this->assertSame([
      'primary' => 'Primary',
      'secondary' => 'Secondary',
    ], $definition['options']);
  }

}

class StoryDiscoveryTestProxy extends StoryDiscovery {

  public function publicBuildFieldDefinition(string $key, $value, array $argType): array {
    return $this->buildFieldDefinition($key, $value, $argType);
  }

}
