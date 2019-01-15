<?php

namespace Drupal\Tests\os_classes\ExistingSiteJavascript;

use Behat\Mink\Exception\ExpectationException;

/**
 * Tests os_classes module.
 *
 * @group classes
 * @group functional-javascript
 * @coversDefaultClass \Drupal\os_classes\Form\SemesterFieldOptionsForm
 */
class ClassesAdminFormTest extends ClassesExistingSiteJavascriptTestBase {

  /**
   * Tests os_classes admin form access.
   */
  public function testAccessDeniedAdminForm() {

    // Create a non-admin user
    $user = $this->createUser();
    $this->drupalLogin($user);
    $this->visit('/admin/config/openscholar/classes/field-allowed-values');

    $web_assert = $this->assertSession();

    try {
      $web_assert->statusCodeEquals(403);
    }
    catch (ExpectationException $e) {
      $this->fail(sprintf("Test failed: %s\nBacktrace: %s", $e->getMessage(), $e->getTraceAsString()));
    }
  }
  /**
   * Tests os_classes admin form access.
   */
  public function testAccessAdminForm() {

    $user = $this->createUser(['administer site configuration']);
    $this->drupalLogin($user);
    $this->visit('/admin/config/openscholar/classes/field-allowed-values');

    $web_assert = $this->assertSession();

    try {
      $web_assert->statusCodeEquals(200);
    }
    catch (ExpectationException $e) {
      $this->fail(sprintf("Test failed: %s\nBacktrace: %s", $e->getMessage(), $e->getTraceAsString()));
    }
  }

}
