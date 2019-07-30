<?php

namespace Drupal\Tests\os\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Tests post-logout behavior.
 *
 * @group functional-javascript
 * @group os
 */
class PostLogoutRedirectTest extends OsExistingSiteJavascriptTestBase {

  /**
   * @covers ::os_link_alter
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function test(): void {
    $account = $this->createUser();
    $node = $this->createNode();
    $this->group->addContent($node, "group_node:{$node->bundle()}");
    /** @var \Drupal\Core\Path\AliasManagerInterface $path_alias_manager */
    $path_alias_manager = $this->container->get('path.alias_manager');

    $this->drupalLogin($account);
    $this->visitViaVsite("node/{$node->id()}", $this->group);
    $this->getSession()->getPage()->clickLink($account->getAccountName());
    $this->getSession()->getPage()->clickLink('Log out');

    $this->assertContains("{$this->groupAlias}{$path_alias_manager->getAliasByPath("/node/{$node->id()}")}", $this->getSession()->getCurrentUrl());
  }

}
