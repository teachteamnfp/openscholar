<?php

namespace Drupal\Tests\cp\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Smoke tests the UI of cp settings collection.
 *
 * @covers \Drupal\cp\Controller\CpController
 * @covers \Drupal\cp\CpManager
 *
 * @group cp
 * @group functional
 */
class CpSettingsCollectionTest extends OsExistingSiteTestBase {

  /**
   * Smoke tests the UI of cp settings collection.
   *
   * @covers \Drupal\cp\Controller\CpController::overview
   * @covers \Drupal\cp\CpManager::getBlockContents
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function test(): void {
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);

    $this->drupalLogin($group_admin);

    $this->visitViaVsite('cp/appearance', $this->group);
    $this->assertSession()->pageTextContains('Appearance Settings');
    $this->assertSession()->linkExists('Breadcrumbs');
    $this->assertSession()->linkExists('Themes');
  }

}
