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
   * Group content type.
   *
   * @var \Drupal\group\Entity\GroupContentTypeInterface
   */
  protected $plugin;

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
    $this->plugin = reset($plugins);

    $this->group = $this->createGroup();
    $this->group->save();

    $config_factory = $this->container->get('config.factory');
    /** @var \Drupal\Core\Config\Config $config */
    $config = $config_factory->getEditable('vsite_infinite_scroll.settings');
    $config->set('long_list_content_pagination', 'infinite_scroll');
    $config->save(TRUE);
  }

  /**
   * Tests vsite_infinite_scroll people view load more button ajax request.
   */
  public function testClickOnLoadMoreButton(): void {
    // Create required nodes.
    $i = 0;
    while ($i < 21) {
      $person = $this->createNode([
        'type' => 'person',
        'status' => 1,
      ]);
      $this->group->addContent($person, $this->plugin->getContentPluginId());
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
    $this->group->addContent($old_person, $this->plugin->getContentPluginId());

    // Test Load more button.
    $web_assert = $this->assertSession();

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

  /**
   * Tests that on scrolling headers do not repeat.
   */
  public function testInfiniteScrollOnPublicationListing(): void {

    // Create required publications.
    $i = 0;
    while ($i < 11) {
      $publication = $this->createReference();
      $this->group->addContent($publication, 'group_entity:bibcite_reference');
      $i++;
    }
    $new_publication = $this->createReference([
      'html_title' => 'New created publication',
    ]);
    $this->group->addContent($new_publication, 'group_entity:bibcite_reference');

    $groupAdmin = $this->createUser();
    $this->addGroupAdmin($groupAdmin, $this->group);
    $this->drupalLogin($groupAdmin);

    $web_assert = $this->assertSession();
    $this->visitViaVsite("publications", $this->group);
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextContains('Artwork');
    $web_assert->elementsCount('css', '.view-publications h3', 1);
    $this->scrollTo(500);
    $this->getSession()->wait(500);
    $web_assert->pageTextContains('New created publication');
    $web_assert->elementsCount('css', '.view-publications h3', 1);
  }

  /**
   * Scroll to a pixel offset.
   *
   * @param int $pixels
   *   The pixel offset to scroll to.
   */
  protected function scrollTo($pixels) {
    $this->getSession()->getDriver()->executeScript("window.scrollTo(null, $pixels);");
  }

}
