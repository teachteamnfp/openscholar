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

  /**
   * Creates an Event entity.
   *
   * @return string
   *   The url to newly created entity.
   */
  public function createEventFunctionalJs(): string {
    $date = new DateTimePlus('+5 days');

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node/add/events');
    $edit = [
      'title[0][value]' => $this->randomString(),
      'field_recurring_date[0][day_start]' => $date->format("Y-m-d"),
      'field_recurring_date[0][day_end]' => $date->format("Y-m-d"),
      'field_recurring_date[0][is_all_day]' => TRUE,
      'field_signup[value]' => TRUE,
    ];
    $this->submitForm($edit, 'edit-submit');
    return $this->getUrl();
  }

  /**
   * Creates an Event entity.
   *
   * @return string
   *   The url to newly created entity.
   */
  public function createRecurringEvent(): string {
    $date = new DateTimePlus('+5 day');

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node/add/events');
    $edit = [
      'title[0][value]' => $this->randomString(),
      'field_recurring_date[0][day_start]' => $date->format("Y-m-d"),
      'field_recurring_date[0][day_end]' => $date->format("Y-m-d"),
      'field_recurring_date[0][is_all_day]' => TRUE,
      'field_recurring_date[0][recurrence_option]' => 'daily',
      'field_recurring_date[0][ends_mode]' => TRUE,
      'field_recurring_date[0][ends_count]' => 3,
      'field_signup[value]' => TRUE,
      'field_singup_multiple[value]' => TRUE,
    ];
    $this->submitForm($edit, 'edit-submit');
    return $this->getUrl();
  }

}
