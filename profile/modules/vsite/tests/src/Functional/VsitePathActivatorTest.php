<?php

namespace Drupal\Tests\vsite\Functional;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests VsitePathActivator.
 *
 * @group functional
 * @group vsite
 * @coversDefaultClass \Drupal\vsite\Plugin\VsitePathActivator
 */
class VsitePathActivatorTest extends ExistingSiteBase {

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
