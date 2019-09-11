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
   * @throws \Drupal\Core\Entity\EntityStorageException
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
    $this->assertStringEndsWith("destination={$this->groupAlias}", $this->getSession()->getCurrentUrl());
  }

  /**
   * Tests the redirect when the destination is going to be a cp setting.
   *
   * @covers ::os_link_alter
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testCpSettingDestination(): void {
    $content = $this->createNode();
    $this->group->addContent($content, "group_node:{$content->bundle()}");
    $account = $this->createUser();
    $this->addGroupAdmin($account, $this->group);

    // Tests.
    $this->drupalLogin($account);
    $this->visitViaVsite('cp/content', $this->group);
    $this->getSession()->getPage()->clickLink($account->getAccountName());
    $this->getSession()->getPage()->clickLink('Log out');

    $this->assertStringEndsWith($this->groupAlias, $this->getSession()->getCurrentUrl());
  }

  /**
   * Tests whether login redirect is correct for private vsite.
   *
   * @covers ::os_preprocess_block
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testPrivateVsiteLoginRedirect(): void {
    // Setup.
    $private_vsite = $this->createPrivateGroup();
    $private_vsite_alias = $private_vsite->get('path')->first()->getValue()['alias'];

    // Tests.
    $this->visitViaVsite('publications', $private_vsite);

    $login_link_inside_message = $this->getSession()->getPage()->findLink('log in here');
    $login_link_inside_message->click();
    $this->assertStringEndsWith("destination={$private_vsite_alias}/publications", $this->getSession()->getCurrentUrl());

    $this->visitViaVsite('publications', $private_vsite);

    $login_link_inside_footer = $this->getSession()->getPage()->findLink('Admin Login');
    $login_link_inside_footer->click();
    $this->assertStringEndsWith("destination={$private_vsite_alias}/publications", $this->getSession()->getCurrentUrl());
  }

}
