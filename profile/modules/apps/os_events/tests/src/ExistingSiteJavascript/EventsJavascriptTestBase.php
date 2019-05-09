<?php

namespace Drupal\Tests\os_events\ExistingSiteJavascript;

use Drupal\Component\Datetime\DateTimePlus;
use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * EventsJavascriptTestBase.
 */
class EventsJavascriptTestBase extends ExistingSiteWebDriverTestBase {
  /**
   * Simple user.
   *
   * @var \Drupal\user\Entity\User|false
   */
  protected $simpleUser;
  /**
   * Admin User.
   *
   * @var \Drupal\user\Entity\User|false
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->adminUser = $this->createUser([], '', TRUE);
  }

  /**
   * Creates an Event entity.
   *
   * @return string
   *   The url to newly created entity.
   */
  public function createEvent() {
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
    $node_url = $this->getUrl();
    return $node_url;
  }

  /**
   * Creates an Event entity.
   *
   * @return string
   *   The url to newly created entity.
   */
  public function createRecurringEvent() {
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
    $node_url = $this->getUrl();
    return $node_url;
  }

}
