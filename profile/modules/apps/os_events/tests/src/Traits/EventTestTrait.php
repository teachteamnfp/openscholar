<?php

namespace Drupal\Tests\os_events\Traits;

use Drupal\node\NodeInterface;

/**
 * Helper methods for os_events tests.
 */
trait EventTestTrait {

  /**
   * Creates an event.
   *
   * @param array $values
   *   The values used to create the event.
   *
   * @return \Drupal\node\NodeInterface
   *   The created event entity.
   */
  protected function createEvent(array $values = []) : NodeInterface {
    $event = $this->createNode($values + [
      'type' => 'events',
      'title' => $this->randomMachineName(),
    ]);

    return $event;
  }

}
