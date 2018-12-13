<?php

namespace Drupal\Tests\vsite\Functional;

use Drupal\Tests\group\Functional\GroupBrowserTestBase;

/**
 * Tests VsitePathActivator.
 *
 * @group functional
 * @group vsite
 * @coversDefaultClass \Drupal\vsite\Plugin\VsitePathActivator
 */
class VsitePathActivatorTest extends GroupBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  protected $profile = 'openscholar';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'group',
    'purl',
    'group_purl',
    'vsite',
  ];

  /**
   * Tests modifier matched event.
   */
  public function testModifierMatched() {
    $group = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => 'test-alias',
      ],
    ]);

    $this->drupalGet('/test-alias');
  }

}
