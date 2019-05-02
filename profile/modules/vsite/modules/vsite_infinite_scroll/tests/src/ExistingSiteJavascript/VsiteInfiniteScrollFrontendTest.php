<?php

namespace Drupal\Tests\vsite_infinite_scroll\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Tests vsite_infinite_scroll module.
 *
 * @group functional-javascript
 * @group vsite
 */
class VsiteInfiniteScrollFrontendTest extends OsExistingSiteJavascriptTestBase {

  /**
   * Group parent test entity.
   *
   * @var \Drupal\group\Entity\Group
   */
  protected $group;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $entity_type_manager = $this->container->get('entity_type.manager');

    // Set the current user so group creation can rely on it.
    $this->container->get('current_user')->setAccount($this->createUser());

    // Enable the user_as_content plugin on the default group type.
    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    $storage = $entity_type_manager->getStorage('group_content_type');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface[] $plugin */
    $plugins = $storage->loadByContentPluginId('group_node:person');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface $plugin */
    $plugin = reset($plugins);

    $this->group = $this->createGroup();
    $this->group->save();

    $i = 0;
    while ($i < 21) {
      $person = $this->createNode([
        'type' => 'person',
        'status' => 1,
      ]);
      $this->group->addContent($person, $plugin->getContentPluginId());
      $i++;
    }
    $old_person = $this->createNode([
      'title' => 'Old created person',
      'type' => 'person',
      'status' => 1,
      'changed' => strtotime('-1 Year'),
      'created' => strtotime('-1 Year'),
      'field_first_name' => 'Old',
      'field_last_name' => 'Man',
    ]);
    $this->group->addContent($old_person, $plugin->getContentPluginId());
  }

  /**
   * Tests vsite_infinite_scroll people view load more button ajax request.
   */
  public function testClickOnLoadMoreButton(): void {
    $web_assert = $this->assertSession();
    $config_factory = $this->container->get('config.factory');
    /** @var \Drupal\Core\Config\Config $config */
    $config = $config_factory->getEditable('vsite_infinite_scroll.settings');
    $config->set('long_list_content_pagination', 'infinite_scroll');
    $config->save(TRUE);

    $path = $this->group->get('path')->getValue();
    $alias = $path[0]['alias'];
    $this->visit($alias . "/people");
    $web_assert->statusCodeEquals(200);

    $page = $this->getCurrentPage();
    $checkHtmlValue = $page->hasContent('Load More');
    $this->assertTrue($checkHtmlValue, 'Load More button has not found.');

    $load_button = $page->findLink('Load More');
    $load_button->press();
    $this->waitForAjaxToFinish();
    $load_button = $page->findLink('Load More');
    $load_button->press();
    $this->waitForAjaxToFinish();

    $result = $web_assert->waitForElementVisible('named', ['link', 'Old Man']);
    $this->assertNotNull($result, 'Following node title not found: Old created person');
  }

}
