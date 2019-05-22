<?php

namespace Drupal\Tests\os_events\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;
use Drupal\Tests\os_events\Traits\EventTestTrait;

/**
 * EventsJavascriptTestBase.
 */
class EventsJavascriptTestBase extends OsExistingSiteJavascriptTestBase {

  use EventTestTrait;

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

}
