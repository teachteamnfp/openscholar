<?php

namespace Drupal\Tests\os_events\Traits;

use Drupal\Component\Datetime\DateTimePlus;
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

  /**
   * Creates an event.
   *
   * @param bool $signupChecked
   *   If Signup is checked or not.
   */
  protected function createEventFunctional(bool $signupChecked): void {
    $date = new DateTimePlus('+5 days', $this->config->get('system.date')->get('timezone.default'));

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node/add/events');
    $edit = [
      'title[0][value]' => $this->randomString(),
      'field_recurring_date[0][day_start]' => $date->format("Y-m-d"),
      'field_recurring_date[0][is_all_day]' => TRUE,
      'field_recurring_date[0][day_end]' => $date->format("Y-m-d"),
      'field_signup[value]' => $signupChecked,
    ];
    $this->submitForm($edit, 'edit-submit');
  }

}
