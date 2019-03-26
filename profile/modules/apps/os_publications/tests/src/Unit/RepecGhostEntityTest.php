<?php

namespace Drupal\Tests\os_publications\Unit;

use Drupal\os_publications\GhostEntity\Repec;
use Drupal\Tests\UnitTestCase;

/**
 * RepecGhostEntityTest.
 *
 * @group unit
 */
class RepecGhostEntityTest extends UnitTestCase {

  /**
   * Tests repec ghost entity.
   */
  public function testEntity() {
    $entity = new Repec(47, 'bibcite_reference', 'artwork');

    $this->assertSame(47, $entity->id());
    $this->assertSame('bibcite_reference', $entity->type());
    $this->assertSame('artwork', $entity->bundle());
  }

}
