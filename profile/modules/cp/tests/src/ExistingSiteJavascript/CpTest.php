<?php

namespace Drupal\Tests\cp\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Control panel toolbar tests.
 *
 * @group functional-javascript
 * @group cp
 */
class CpTest extends OsExistingSiteJavascriptTestBase {

  /**
   * Tests visibility of vsite actions by group admins.
   *
   * @covers ::cp_toolbar_alter
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testVisibility(): void {
    // Setup.
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);

    $this->drupalLogin($group_admin);

    // Tests.
    $this->visitViaVsite('', $this->group);

    $this->getSession()->getPage()->findLink('Control Panel')->click();
    $this->assertSession()->pageTextContains('Site Content');

    $vsite_content_link = $this->getSession()->getPage()->findLink('Site Content');
    $vsite_content_link->mouseOver();
    $this->assertSession()->pageTextContains('Add');

    $vsite_content_add_link = $this->getSession()->getPage()->findLink('Add');
    $vsite_content_add_link->mouseOver();
    $this->assertSession()->pageTextContains('Blog');
  }

  /**
   * Tests visibility of vsite content by anonymous.
   */
  public function testVisibilityCpContentAnonymous(): void {
    $web_assert = $this->assertSession();
    // Tests.
    $this->visitViaVsite('cp/content', $this->group);
    // Go to edit path.
    $page = $this->getCurrentPage();
    $login_link = $page->findLink('Admin Login');
    $this->assertNotNull($login_link);
    $web_assert->statusCodeEquals(403);
  }

}
