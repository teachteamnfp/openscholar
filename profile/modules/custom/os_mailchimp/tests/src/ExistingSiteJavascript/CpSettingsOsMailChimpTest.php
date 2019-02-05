<?php

namespace Drupal\Tests\os_mailchimp\ExistingSiteJavascript;

use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * Tests os_mailchimp module.
 *
 * @group mailchimp
 * @group functional-javascript
 */
class CpSettingsOsMailChimpTest extends ExistingSiteWebDriverTestBase {

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
   * Tests os_mailchimp cp settings form submit and default value.
   */
  public function testCpSettingsFormSave() {
    $web_assert = $this->assertSession();
    $this->drupalLogin($this->adminUser);

    $this->visit("/cp/settings/mailchimp");
    $web_assert->statusCodeEquals(200);

    $edit = [
      'api_key' => 'test1234',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $page = $this->getCurrentPage();
    $checkHtmlValue = $page->hasContent('The configuration options have been saved.');
    $this->assertTrue($checkHtmlValue, 'The form did not write the correct message.');

    // Check form elements load default values.
    $this->visit("/cp/settings/mailchimp");
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $fieldValue = $page->findField('api_key')->getValue();
    $this->assertSame('test1234', $fieldValue, 'Form is not loaded api key value.');
  }

  /**
   * Tests block visibility and modal popup.
   */
  public function testBlockVisibilityInContentRegion() {
    $web_assert = $this->assertSession();
    $this->visit("/");
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $isExists = $page->hasContent('Mailchimp subscribe');
    $this->assertTrue($isExists, 'Region not contains mailchimp block.');

    // Subscribe link is visible and press it.
    $submit_button = $page->findLink('Subscribe to list!');
    $submit_button->press();

    // Check modal is appeared.
    $result = $web_assert->waitForElementVisible('css', '.ui-dialog');
    $this->assertNotNull($result);
  }

}
