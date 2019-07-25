<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class PublicationMenusTest.
 *
 * @group publications
 * @group kernel
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 */
class PublicationMenusTest extends OsExistingSiteTestBase {
  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * Database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Menu Link manager service.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLink;

  /**
   * Menu Helper service.
   *
   * @var \Drupal\cp_menu\MenuHelperInterface
   */
  protected $menuHelper;

  /**
   * Reference interface.
   *
   * @var \Drupal\bibcite_entity\Entity\ReferenceInterface
   */
  protected $reference;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->group = $this->createGroup();
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->drupalLogin($this->groupAdmin);
    $this->reference = $this->createReference();
    $this->group->addContent($this->reference, 'group_entity:bibcite_reference');

    $this->menuHelper = $this->container->get('cp_menu.menu_helper');
    $this->database = $this->container->get('database');
    $this->menuLink = $this->container->get('plugin.manager.menu.link');

  }

  /**
   * Tests menu defaults service.
   */
  public function testMenuDefaultsService(): void {
    $this->menuHelper->createVsiteMenus($this->group);
    // Create a new menu_link_content entity.
    MenuLinkContent::create([
      'link' => ['uri' => 'entity:bibcite_reference/' . $this->reference->id()],
      'langcode' => $this->reference->language()->getId(),
      'enabled' => TRUE,
      'title' => 'Test Title Menu',
      'description' => 'This is a test',
      'menu_name' => 'menu-primary-' . $this->group->id(),
    ])->save();

    // Test the service.
    $defaults = $this->menuHelper->getMenuLinkDefaults($this->reference, $this->group);
    $this->assertArrayHasKey('entity_id', $defaults);
    $this->assertArrayHasKey('id', $defaults);
    $this->assertArrayHasKey('title', $defaults);
    $this->assertArrayHasKey('description', $defaults);
  }

  /**
   * Tests publication entity link creation edit form service.
   */
  public function testPublicationMenuService(): void {
    $values['menu']['menu_parent'] = 'main';
    $values['menu']['id'] = '';
    $values['menu']['enabled'] = TRUE;
    $values['menu']['title'] = 'Menu Test Title';
    $values['menu']['description'] = 'Test desc';

    // Test new link creation.
    $this->menuHelper->publicationInFormMenuAlterations($values, $this->reference, $this->group);
    $query = $this->database
      ->select('menu_link_content_data', 'mlcd')
      ->fields('mlcd', [
        'title',
        'id',
      ]);
    $query->condition('mlcd.link__uri', "entity:bibcite_reference/" . $this->reference->id());
    $menus = $query->execute()->fetchAssoc();
    $this->assertNotNull($menus);

    // Test existing link update.
    $values['menu']['title'] = 'Menu Test Title Changed';
    $values['menu']['menu_parent'] = 'menu-primary-' . $this->group->id();
    $id = $query->execute()->fetchField(1);
    $pluginId = MenuLinkContent::load($id)->getPluginId();
    $values['menu']['id'] = $pluginId;
    $this->menuHelper->publicationInFormMenuAlterations($values, $this->reference, $this->group);
    $changed_title = $this->menuLink->getDefinition($pluginId)['title']->__toString();
    $this->assertSame($values['menu']['title'], $changed_title);

    // Test existing link deletion.
    $values['menu']['enabled'] = FALSE;
    $this->menuHelper->publicationInFormMenuAlterations($values, $this->reference, $this->group);
    $this->assertFalse($query->execute()->fetchAssoc());
  }

}
