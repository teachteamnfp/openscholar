<?php

namespace Drupal\Tests\vsite_infinite_scroll\ExistingSiteJavascript;

/**
 * Tests vsite_infinite_scroll module.
 *
 * @group vsite
 * @group functional-javascript
 */
class VsiteInfiniteScrollFrontendTest extends VsiteInfiniteScrollExistingSiteJavascriptTestBase {

  /**
   * Group parent test entity.
   *
   * @var \Drupal\group\Entity\Group
   */
  protected $group;

  private $lastPersonTitle = '';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Set the current user so group creation can rely on it.
    $this->container->get('current_user')->setAccount($this->createUser());

    // Enable the user_as_content plugin on the default group type.
    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_content_type');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface[] $plugin */
    $plugins = $storage->loadByContentPluginId('group_node:person');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface $plugin */
    $plugin = reset($plugins);

    $this->group = $this->createGroup([
      'type' => 'personal',
      'title' => 'Site01',
      'path' => '/site01',
    ]);
    $this->group->save();

    $i = 0;
    while ($i < 11) {
      $person = $this->createNode([
        'type' => 'person',
        'status' => 1,
      ]);
      $this->group->addContent($person, $plugin->getContentPluginId());
      $i++;
      $this->lastPersonTitle = $person->getTitle();
    }
  }

  /**
   * Tests vsite_infinite_scroll people view load more button ajax request.
   */
  public function testClickOnLoadMoreButton() {
    $web_assert = $this->assertSession();
    $this->config->set('long_list_content_pagination', 'infinite_scroll');
    $this->config->save(TRUE);

    $path = $this->group->get('path')->getValue();
    $alias = $path[0]['alias'];
    $this->visit($alias . "/people");
    $web_assert->statusCodeEquals(200);

    $page = $this->getCurrentPage();
    $checkHtmlValue = $page->hasContent('Load More');
    $this->assertTrue($checkHtmlValue, 'Load More button has not found.');

    $load_button = $page->findLink('Load More');
    $load_button->press();
    $result = $web_assert->waitForElementVisible('named', ['link', $this->lastPersonTitle]);
    $this->assertNotNull($result);
  }

}
