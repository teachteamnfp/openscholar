<?php

namespace Drupal\Tests\os_events\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Drupal\Tests\os_events\Traits\EventTestTrait;

/**
 * Test base for event tests.
 */
abstract class EventsTestBase extends OsExistingSiteTestBase {

  use EventTestTrait;

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

}
