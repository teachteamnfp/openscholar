<?php

namespace Drupal\Tests\openscholar\ExistingSite;

use Drupal\Tests\openscholar\Traits\ExistingSiteTestTrait;
use Drupal\Tests\TestFileCreationTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * OS kernel and functional test base.
 */
abstract class OsExistingSiteTestBase extends ExistingSiteBase {

  use ExistingSiteTestTrait;
  use TestFileCreationTrait;

  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->group = $this->createGroup();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    parent::tearDown();

    foreach ($this->cleanUpConfigs as $config_entity) {
      $config_entity->delete();
    }
  }

}
