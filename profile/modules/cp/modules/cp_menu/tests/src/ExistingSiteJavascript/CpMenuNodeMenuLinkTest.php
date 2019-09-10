<?php

namespace Drupal\Tests\cp_menu\ExistingSiteJavaScript;

use Drupal\cp_menu\Services\MenuHelper;
use Drupal\group\Entity\GroupInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\NodeInterface;
use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Tests whether menu link can be created while creating a vsite node.
 *
 * @covers ::cp_menu_form_node_form_alter
 * @covers ::cp_menu_form_node_form_submit
 *
 * @group functional-javascript
 * @group cp-menu
 */
class CpMenuNodeMenuLinkTest extends OsExistingSiteJavascriptTestBase {

  /**
   * Menu link content storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $menuLinkContentStorage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');
    $this->menuLinkContentStorage = $entity_type_manager->getStorage('menu_link_content');

    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);

    $this->drupalLogin($group_admin);
  }

  /**
   * Tests - No vsite menu link if user does not chooses to create menu link.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testNoMenuCreation(): void {
    $title = $this->randomMachineName();

    $this->visitViaVsite('node/add/blog', $this->group);

    // Setup.
    $menu_create_option = $this->getSession()->getPage()->find('css', '#edit-menu summary');
    $this->assertNotNull($menu_create_option);
    $menu_create_option->click();

    $this->submitForm([
      'title[0][value]' => $title,
    ], 'Save');

    $node = $this->loadNodeByTitle($title);

    // Tests.
    $this->assertCount(0, $this->getVsiteNodeMenuLinkContents($node, "menu-primary-{$this->group->id()}"));
    $this->assertCount(0, $this->getVsiteNodeMenuLinkContents($node, "menu-secondary-{$this->group->id()}"));

    // Clean up.
    $node->delete();
    $this->cleanUpVsiteMenus($this->group);
  }

  /**
   * Tests - Top level node menu link creation.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testTopLevelMenuCreation(): void {
    $title = $this->randomMachineName();

    $this->visitViaVsite('node/add/blog', $this->group);

    // Setup.
    $menu_create_option = $this->getSession()->getPage()->find('css', '#edit-menu summary');
    $this->assertNotNull($menu_create_option);
    $menu_create_option->click();

    $this->submitForm([
      'title[0][value]' => $title,
      'menu[enabled]' => 1,
      'menu[title]' => $title,
      'menu[description]' => '',
      'menu[menu_parent]' => 'main:',
    ], 'Save');

    $node = $this->loadNodeByTitle($title);
    $menu_link_contents = $this->getVsiteNodeMenuLinkContents($node, "menu-primary-{$this->group->id()}");
    $menu_link_content = reset($menu_link_contents);

    // Tests.
    $this->assertNotNull($menu_link_content);
    $this->assertInstanceOf(MenuLinkContent::class, $menu_link_content);

    // Clean up.
    $node->delete();
    $this->cleanUpVsiteMenus($this->group);
  }

  /**
   * Tests - Node menu link creation as child.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testChildMenuCreation(): void {
    $title = $this->randomMachineName();

    $this->visitViaVsite('node/add/blog', $this->group);

    $menu_create_option = $this->getSession()->getPage()->find('css', '#edit-menu summary');
    $this->assertNotNull($menu_create_option);
    $menu_create_option->click();

    $this->submitForm([
      'title[0][value]' => $title,
      'menu[enabled]' => 1,
      'menu[title]' => $title,
      'menu[description]' => '',
      'menu[menu_parent]' => 'main:views_view:views.publications.page_1',
      'menu[weight]' => 0,
    ], 'Save');

    $node = $this->loadNodeByTitle($title);
    $menu_link_contents = $this->getVsiteNodeMenuLinkContents($node, "menu-primary-{$this->group->id()}");
    $menu_link_content = reset($menu_link_contents);

    $vsite_parent_menu = $this->menuLinkContentStorage->loadByProperties([
      'menu_name' => "menu-primary-{$this->group->id()}",
      'link__uri' => 'route:view.publications.page_1',
    ]);
    /** @var \Drupal\menu_link_content\MenuLinkContentInterface $vsite_parent_menu_link_content */
    $vsite_parent_menu_link_content = reset($vsite_parent_menu);

    // Tests.
    $this->assertNotNull($menu_link_content);
    $this->assertInstanceOf(MenuLinkContent::class, $menu_link_content);
    $this->assertEquals($vsite_parent_menu_link_content->getPluginId(), $menu_link_content->get('parent')->first()->getValue()['value']);

    // Clean up.
    $node->delete();
    $this->cleanUpVsiteMenus($this->group);
  }

  /**
   * Returns vsite menu link contents for a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param string $vsite_menu_name
   *   The vsite menu name.
   *
   * @return \Drupal\menu_link_content\MenuLinkContentInterface[]
   *   Menu link content entities.
   */
  protected function getVsiteNodeMenuLinkContents(NodeInterface $node, string $vsite_menu_name): array {
    return $this->menuLinkContentStorage->loadByProperties([
      'menu_name' => $vsite_menu_name,
      'link__uri' => "entity:node/{$node->id()}",
    ]);
  }

  /**
   * Cleans up vsite menus created during test.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The vsite.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function cleanUpVsiteMenus(GroupInterface $group): void {
    foreach (MenuHelper::DEFAULT_VSITE_MENU_MAPPING as $item) {
      $menu_link_contents = $this->menuLinkContentStorage->loadByProperties([
        'menu_name' => "$item{$group->id()}",
      ]);

      $this->menuLinkContentStorage->delete($menu_link_contents);
    }
  }

}
