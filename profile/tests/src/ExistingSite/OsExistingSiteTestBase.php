<?php

namespace Drupal\Tests\openscholar\ExistingSite;

use Drupal\Tests\openscholar\Traits\ExistingSiteTestTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * OS kernel and functional test base.
 */
abstract class OsExistingSiteTestBase extends ExistingSiteBase {

  use ExistingSiteTestTrait;

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
      // This is part of the test cleanup.
      // If this is not done, then it leads to database deadlock error in the
      // test. The test is performing nested db operations during cleanup.
      $menus = $this->group->getContent('group_menu:menu');
      foreach ($menus as $menu) {
        $menu->delete();
      }
      $contents = $this->group->getContent();
      foreach ($contents as $content) {
        $content->delete();
      }
      $this->groupAdmin->delete();
      $this->group->delete();
    }
  }

}
