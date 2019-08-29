<?php

namespace Drupal\Tests\os_app_access\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Test base for app access tests.
 */
abstract class AppAccessTestBase extends OsExistingSiteTestBase {

  /**
   * Default app accesses.
   *
   * @var array
   */
  protected $defaultAppAccesses;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');
    $this->defaultAppAccesses = $config_factory->get('os_app_access.access')->getRawData();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');
    /** @var \Drupal\Core\Config\Config $mut_app_access_config */
    $mut_app_access_config = $config_factory->getEditable('os_app_access.access');
    $mut_app_access_config->setData($this->defaultAppAccesses)->save(TRUE);

    parent::tearDown();
  }

}
