<?php

namespace Drupal\Tests\os\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * PrivateVsiteGuard test.
 *
 * @coversDefaultClass \Drupal\os\PrivateVsiteGuard
 *
 * @group functional
 * @group os
 */
class PrivateVsiteGuardFunctionalTest extends OsExistingSiteTestBase {

  /**
   * @covers ::access
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function test(): void {
    $public_group = $this->group;
    $private_group = $this->createPrivateGroup();

    $this->visitViaVsite('news', $public_group);
    $this->assertSession()->statusCodeEquals(200);

    $this->visit('/');
    $this->assertSession()->statusCodeEquals(200);

    $this->visitViaVsite('news', $private_group);
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSession()->pageTextContains('This website or page content is accessible to authorized users. For access, please log in here.');
  }

}
