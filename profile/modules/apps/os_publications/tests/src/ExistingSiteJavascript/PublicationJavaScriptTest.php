<?php

namespace Drupal\Tests\os_publications\ExistingSiteJavascript;

use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * A WebDriver test suitable for testing Ajax and client-side interactions.
 *
 * @group functional-javascript
 */
class PublicationJavaScriptTest extends ExistingSiteWebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->user = $this->createUser([], '', TRUE);
    $this->simpleUser = $this->createUser();
  }

  /**
   * Test show/hide of citation examples.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testPreviewToggle() {
    $this->drupalLogin($this->user);
    $web_assert = $this->assertSession();

    $url = $this->buildUrl('/cp/settings/publications');
    $this->visit($url);
    file_put_contents('public://screenshot.jpg', $this->getSession()->getScreenshot());
    file_put_contents('public://test.html', $this->getCurrentPageContent());

    $page = $this->getCurrentPage();

    // Test Modern Language hover.
    $format = $page->findField('edit-os-publications-preferred-bibliographic-format-modern-language-association');
    $format->mouseOver();
    $result = $web_assert->waitForElementVisible('css', '#modern_language_association');
    $this->assertNotNull($result);
    $value = ucwords(str_replace("_", " ", $result->getValue()));
    // Verify the text on the page.
    $web_assert->pageTextContains($value);

    // Test American Medical hover.
    $format = $page->findField('edit-os-publications-preferred-bibliographic-format-american-medical-association');
    $format->mouseOver();
    $result = $web_assert->waitForElementVisible('css', '#american_medical_association');
    $this->assertNotNull($result);
    $value = ucwords(str_replace("_", " ", $result->getValue()));
    // Verify the text on the page.
    $web_assert->pageTextContains($value);
  }

}
