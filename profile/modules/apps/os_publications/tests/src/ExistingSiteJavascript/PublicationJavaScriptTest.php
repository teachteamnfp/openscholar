<?php

namespace Drupal\Tests\os_publications\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * A WebDriver test suitable for testing Ajax and client-side interactions.
 *
 * @group functional-javascript
 * @group publications
 */
class PublicationJavaScriptTest extends OsExistingSiteJavascriptTestBase {

  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
  }

  /**
   * Test show/hide of citation examples.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testPreviewToggle(): void {
    $this->drupalLogin($this->groupAdmin);
    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/publications");

    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);

    $page = $this->getCurrentPage();

    // Test Modern Language hover.
    $format = $page->findField('edit-os-publications-preferred-bibliographic-format-modern-language-association');
    $format->mouseOver();
    $result = $web_assert->waitForElementVisible('css', '#modern_language_association');
    $this->assertNotNull($result);
    $value = ucwords(str_replace('_', ' ', $result->getValue()));
    // Verify the text on the page.
    $web_assert->pageTextContains($value);

    // Test American Medical hover.
    $format = $page->findField('edit-os-publications-preferred-bibliographic-format-american-medical-association');
    $format->mouseOver();
    $result = $web_assert->waitForElementVisible('css', '#american_medical_association');
    $this->assertNotNull($result);
    $value = ucwords(str_replace('_', ' ', $result->getValue()));
    // Verify the text on the page.
    $web_assert->pageTextContains($value);
  }

}
