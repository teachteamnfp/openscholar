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
   * Group Plugin manager.
   *
   * @var \Drupal\group\Plugin\GroupContentEnablerManager
   */
  protected $pluginManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->group = $this->createGroup();
    $this->pluginManager = $this->container->get('plugin.manager.group_content_enabler');
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    foreach ($this->cleanUpConfigs as $config_entity) {
      $config_entity->delete();
    }
    // This is part of the test cleanup.
    // If this is not done, then it leads to database deadlock error in the
    // test. The test is performing nested db operations during cleanup.
    $installed = $this->pluginManager->getInstalledIds($this->group->getGroupType());
    foreach ($this->pluginManager->getAll() as $plugin_id => $plugin) {
      if (in_array($plugin_id, $installed)) {
        $contents = $this->group->getContent($plugin_id);
        foreach ($contents as $content) {
          $content->delete();
        }
      }
    }
    $this->group->delete();

    parent::tearDown();
  }

}
