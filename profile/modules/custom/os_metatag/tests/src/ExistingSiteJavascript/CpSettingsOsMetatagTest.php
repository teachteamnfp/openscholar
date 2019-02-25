<?php

namespace Drupal\Tests\os_metatag\ExistingSiteJavascript;

use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * Tests os_metatag module.
 *
 * @group metatag
 * @group functional-javascript
 */
class CpSettingsOsMetatagTest extends ExistingSiteWebDriverTestBase {

  /**
   * Admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->adminUser = $this->createUser([
      'access administration pages',
      'access control panel',
    ]);
  }

  /**
   * Tests os_metatag cp settings form behavior.
   */
  public function testCpSettingsFormSave() {
    $web_assert = $this->assertSession();
    $this->drupalLogin($this->adminUser);

    $this->visit("/cp/settings/seo");
    $web_assert->statusCodeEquals(200);

    $edit = [
      'site_title' => 'Test Site Title',
      'meta_description' => 'LoremIpsumDolor',
      'publisher_url' => 'http://example-publisher.com/',
      'author_url' => 'http://example-author.com/',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $page = $this->getCurrentPage();
    $checkHtmlValue = $page->hasContent('The configuration options have been saved.');
    $this->assertTrue($checkHtmlValue, 'The form did not write the correct message.');

    // Check form elements load default values.
    $this->visit("/cp/settings/seo");
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $fieldValue = $page->findField('site_title')->getValue();
    $this->assertSame('Test Site Title', $fieldValue, 'Form is not loaded site title value.');
    $fieldValue = $page->findField('meta_description')->getValue();
    $this->assertSame('LoremIpsumDolor', $fieldValue, 'Form is not loaded meta description value.');
    $fieldValue = $page->findField('publisher_url')->getValue();
    $this->assertSame('http://example-publisher.com/', $fieldValue, 'Form is not loaded publisher url value.');
    $fieldValue = $page->findField('author_url')->getValue();
    $this->assertSame('http://example-author.com/', $fieldValue, 'Form is not loaded author url value.');
  }

  /**
   * Tests os_metatag cp settings form behavior.
   */
  public function testHtmlHeadValuesOnFrontPage() {
    $web_assert = $this->assertSession();
    $this->drupalLogin($this->adminUser);
    $this->visit("/cp/settings/seo");
    $this->getSession()->resizeWindow(1440, 900, 'current');
    $this->getSession()->executeScript("window.scrollBy(0,1000)");
    file_put_contents('public://screenshot-2.jpg', $this->getSession()->getScreenshot());
    $web_assert->statusCodeEquals(200);

    $edit = [
      'site_title' => 'Test Site Title<>',
      'meta_description' => 'LoremIpsumDolor<"">',
      'publisher_url' => 'http://example-publisher.com/"\'quote-test<>',
      'author_url' => 'http://example-author.com/"\'quote-test<>',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $web_assert->statusCodeEquals(200);

    $this->drupalLogout();
    drupal_flush_all_caches();
    $this->visit("/");
    $expectedHtmlValue = '<link rel="publisher" href="http://example-publisher.com/&amp;quot;&amp;#039;quote-test&amp;lt;&amp;gt;">';
    $this->assertContains($expectedHtmlValue, $this->getCurrentPageContent(), 'HTML head not contains publisher link.');
    $expectedHtmlValue = '<link rel="author" href="http://example-author.com/&amp;quot;&amp;#039;quote-test&amp;lt;&amp;gt;">';
    $this->assertContains($expectedHtmlValue, $this->getCurrentPageContent(), 'HTML head not contains author link.');
  }

}
