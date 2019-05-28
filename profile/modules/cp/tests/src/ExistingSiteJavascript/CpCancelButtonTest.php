<?php

namespace Drupal\Tests\cp\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * CpCancelButtonTest.
 *
 * @group functional-javascript
 * @group cp
 */
class CpCancelButtonTest extends OsExistingSiteJavascriptTestBase {

  protected $nodePath;
  protected $vsiteAlias;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    /** @var \Drupal\Core\Path\AliasManagerInterface $path_alias_manager */
    $path_alias_manager = $this->container->get('path.alias_manager');
    /** @var \Drupal\Core\Path\AliasStorageInterface $path_alias_storage */
    $path_alias_storage = $this->container->get('path.alias_storage');
    $this->vsiteAlias = $this->group->get('path')->first()->getValue()['alias'];
    $node = $this->createNode();
    $this->group->addContent($node, "group_node:{$node->bundle()}");
    $this->nodePath = $this->vsiteAlias . $path_alias_manager->getAliasByPath('/node/' . $node->id());
    // Fix group alias of the node.
    $path_alias_storage->save('/node/' . $node->id(), $this->nodePath);
  }

  /**
   * Test for visit from node page and press cancel.
   */
  public function testNodeDeleteCancelButtonPage() {
    $session = $this->getSession();
    $web_assert = $this->assertSession();

    // Visit node.
    $this->visit($this->nodePath);
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $edit_link = $page->findLink('Edit');
    $edit_link->press();
    // Go to edit path.
    $page = $this->getCurrentPage();
    $cancel_button = $page->findLink('Cancel');
    // Click to cancel.
    $cancel_button->press();
    $web_assert->statusCodeEquals(200);

    // Assert url is a node path with group alias.
    $url = $session->getCurrentUrl();
    $this->assertEquals('http://apache' . $this->nodePath, $url);
  }

  /**
   * Test for visit from listing page and press cancel.
   */
  public function testNodeDeleteCancelButtonList() {
    $session = $this->getSession();
    $web_assert = $this->assertSession();

    // Visit cp browse path.
    $this->visit($this->vsiteAlias . '/cp/content');
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $edit_link = $page->findLink('Edit node');
    $edit_link->press();
    // Go to edit path.
    $page = $this->getCurrentPage();
    $cancel_button = $page->findLink('Cancel');
    // Click to cancel.
    $cancel_button->press();
    $web_assert->statusCodeEquals(200);

    // Assert url is a browse path with group alias.
    $url = $session->getCurrentUrl();
    $this->assertEquals('http://apache' . $this->vsiteAlias . '/cp/content', $url);
  }

}
