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
   * Tests the redirect when logged out from outside a cp setting.
   *
   * @covers ::os_link_alter
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function testOutsideCpSetting(): void {
    $account = $this->createUser();
    $node = $this->createNode();
    $this->group->addContent($node, "group_node:{$node->bundle()}");
    /** @var \Drupal\Core\Path\AliasManagerInterface $path_alias_manager */
    $path_alias_manager = $this->container->get('path.alias_manager');

    // Tests.
    $this->drupalLogin($account);
    $this->visitViaVsite("node/{$node->id()}", $this->group);
    $this->getSession()->getPage()->clickLink($account->getAccountName());
    $this->getSession()->getPage()->clickLink('Log out');

    $this->assertContains("{$this->groupAlias}{$path_alias_manager->getAliasByPath("/node/{$node->id()}")}", $this->getSession()->getCurrentUrl());
  }

  /**
   * Tests the redirect when logged out from a cp setting.
   *
   * @covers ::os_link_alter
   * @covers ::os_preprocess_block
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function testInsideCpSetting(): void {
    $account = $this->createUser();
    $this->addGroupAdmin($account, $this->group);

    // Tests.
    $this->drupalLogin($account);
    $this->visitViaVsite('cp/appearance/themes', $this->group);
    $this->getSession()->getPage()->clickLink($account->getAccountName());
    $this->getSession()->getPage()->clickLink('Log out');

    $this->assertStringEndsWith($this->groupAlias, $this->getSession()->getCurrentUrl());

    // Also check whether the destination of "Admin Login" is working as
    // expected.
    $this->getSession()->getPage()->clickLink('Admin Login');
    $this->assertStringEndsWith("destination={$this->groupAlias}/node", $this->getSession()->getCurrentUrl());
  }

}
