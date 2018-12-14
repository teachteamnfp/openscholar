<?php

namespace Drupal\Tests\vsite\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests VsitePathActivator.
 *
 * @group vsite
 * @coversDefaultClass \Drupal\vsite\Plugin\VsitePathActivator
 */
class VsitePathActivatorTest extends ExistingSiteBase {

  /**
   * Tests modifier matched event.
   */
  public function testModifierMatched() {
//    $group = $this->createGroup([
//      'type' => 'personal',
//      'path' => [
//        'alias' => 'test-alias',
//      ],
//    ]);

    $this->drupalGet('<front>');
  }

}
