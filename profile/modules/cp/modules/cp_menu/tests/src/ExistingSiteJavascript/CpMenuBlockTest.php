<?php

namespace Drupal\Tests\cp_menu\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * CpMenuBlocksTest.
 *
 * @group functional-javascript
 * @group cp-menu
 */
class CpMenuBlockTest extends OsExistingSiteJavascriptTestBase {

  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * Group Id.
   *
   * @var string
   */
  protected $id;

  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->adminUser = $this->createUser([], '', TRUE);

    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/test-menu',
      ],
    ]);
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->id = $this->group->id();
  }

  /**
   * Tests Menu Block.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testMenuBlock() {

    // Test if link doesn't already exist.
    $this->visit('/test-menu');
    $session = $this->assertSession();
    $session->linkNotExists('Test Calendar Link');
    $this->drupalLogin($this->groupAdmin);

    $this->visit('/test-menu/cp/build/menu');
    $page = $this->getCurrentPage();

    // Test if newly added link is vsisble in the menu block.
    $link = $page->find('css', '#add_new_link');
    $link->click();
    $session->waitForElementVisible('css', '.cp-menu-link-add-form');
    $edit = [
      'link_type' => 'url',
    ];
    $this->submitForm($edit, 'Continue');
    $session->assertWaitOnAjaxRequest();
    $edit = [
      'title' => 'Test Calendar Link',
      'url' => '/calendar',
    ];
    $this->submitForm($edit, 'Finish');
    $session->waitForElementVisible('css', '#cp-build-menu-table');

    // TODO: The cache invalidate should be removed from here.
    // FIXME: https://github.com/openscholar/openscholar/issues/11811
    /** @var \Drupal\Core\Cache\CacheBackendInterface $cache_backend */
    $cache_backend = $this->container->get('cache.render');
    $cache_backend->invalidateAll();
    $this->visit('/test-menu');
    $session->linkExists('Test Calendar Link');
  }

}
