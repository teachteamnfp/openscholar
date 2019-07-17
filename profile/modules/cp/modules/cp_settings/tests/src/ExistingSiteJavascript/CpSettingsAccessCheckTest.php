<?php

namespace Drupal\Tests\cp_settings\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Checks the availability of cp settings for group roles.
 *
 * @group functional-javascript
 * @group cp
 */
class CpSettingsAccessCheckTest extends OsExistingSiteJavascriptTestBase {

  /**
   * @covers \Drupal\cp_settings\Access\CpSettingsAccessCheck::access
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function test(): void {
    // Setup.
    $group_admin = $this->createUser();
    $group_member = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $this->group->addMember($group_member);

    // Tests.
    $this->drupalLogin($group_admin);

    $this->visitViaVsite('', $this->group);

    $this->assertSession()->linkByHrefExists("{$this->groupAlias}/cp/settings/breadcrumb");

    $this->drupalLogout();

    $this->drupalLogin($group_member);

    $this->visitViaVsite('', $this->group);

    $this->assertSession()->linkByHrefNotExists("{$this->groupAlias}/cp/settings/breadcrumb");
  }

}
