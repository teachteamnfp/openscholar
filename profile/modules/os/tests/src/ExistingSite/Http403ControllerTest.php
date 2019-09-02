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
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function test(): void {
    $this->visitViaVsite('cp/users', $this->group);
    $this->assertSession()->pageTextContains('This website or page content is accessible to authorized users. For access, please log in here.');

    $this->drupalLogin($this->createUser());
    $this->visitViaVsite('cp/users', $this->group);
    $this->assertSession()->responseContains('Sorry, you are not authorized to access this page.<br />Please contact the site owner to gain access.');
  }

}
