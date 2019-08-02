<?php

namespace Drupal\Tests\os\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Http403Controller test.
 *
 * @coversDefaultClass \Drupal\os\Controller\Http403Controller
 * @group functional
 * @group os
 */
class Http403ControllerTest extends OsExistingSiteTestBase {

  /**
   * @covers ::render
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function test(): void {
    $this->visitViaVsite('cp/users', $this->group);
    $this->assertSession()->pageTextContains('This website or page content is accessible to authorized users. For access, please log in here.');
  }

}
