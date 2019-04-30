<?php

namespace Drupal\Tests\os_classes\ExistingSiteJavascript;

use Behat\Mink\Exception\ExpectationException;

/**
 * Tests os_classes module.
 *
 * @group functional-javascript
 * @group classes
 * @coversDefaultClass \Drupal\os_classes\Form\SemesterFieldOptionsForm
 */
class ClassesAdminFormTest extends ClassesExistingSiteJavascriptTestBase {

  /**
   * Tests allowed values admin form access denied.
   */
  public function testAccessDeniedAdminForm() {

    // Create a non-admin user.
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
   * Tests allowed values admin form access with admin user and test submit machine key generate.
   */
  public function testAccessAdminForm() {

    $user = $this->createUser(['administer site configuration']);
    $this->drupalLogin($user);
    $this->visit('/admin/config/openscholar/classes/field-allowed-values');

    $web_assert = $this->assertSession();

    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextContains('Semester field options');
    $page = $this->getCurrentPage();
    $checkDefaultValue = $page->hasContent('fall|Fall');
    $this->assertTrue($checkDefaultValue, 'Default value is not loaded on admin form.');

    $edit = [
      'semester_field_options' => 'Test 1',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');
    $web_assert->statusCodeEquals(200);
    $this->visit('/admin/config/openscholar/classes/field-allowed-values');
    $page = $this->getCurrentPage();
    $checkModifiedValue = $page->hasContent('test_1|Test 1');
    $this->assertTrue($checkModifiedValue, 'Modified value is not what expected.');
  }

  /**
   * Tests os_classes admin form with exists values.
   */
  public function testExistsDataAdminForm() {
    $user = $this->createUser(['administer site configuration']);
    $this->createClass([
      'field_semester' => 'fall',
    ]);
    $this->drupalLogin($user);
    $this->visit('/admin/config/openscholar/classes/field-allowed-values');

    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $checkWarningMessage = $page->hasContent('There are already contents in the class content type!');
    $this->assertTrue($checkWarningMessage, 'Warning message does not appeared.');
  }

}
