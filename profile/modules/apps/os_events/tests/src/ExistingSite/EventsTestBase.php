<?php

namespace Drupal\Tests\os_events\ExistingSite;

use Drupal\Component\Datetime\DateTimePlus;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test base for event tests.
 */
abstract class EventsTestBase extends ExistingSiteBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Admin User.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->adminUser = $this->createUser([], '', TRUE);
    $this->config = $this->container->get('config.factory');

  }

  /**
   * Creates an event.
   *
   * @param bool $signupChecked
   *   If Signup is checked or not.
   */
  protected function createEvent(bool $signupChecked) {
    $date = new DateTimePlus('+5 days', $this->config->get('system.date')->get('timezone.default'));

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node/add/events');
    $edit = [
      'title[0][value]' => $this->randomString(),
      'field_recurring_date[0][value][date]' => $date->format("Y-m-d H:i:s"),
      'field_signup[value]' => $signupChecked,
    ];
    $this->submitForm($edit, 'edit-submit');
  }

}
