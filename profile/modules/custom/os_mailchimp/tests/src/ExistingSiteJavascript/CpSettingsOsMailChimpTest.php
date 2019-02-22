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
    $web_assert->statusCodeEquals(403);
    drupal_flush_all_caches();
    $this->visit("/cp/settings/mailchimp");
    $web_assert->statusCodeEquals(200);

    $edit = [
      'api_key' => 'test1234',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $page = $this->getCurrentPage();
    $check_html_value = $page->hasContent('The configuration options have been saved.');
    $this->assertTrue($check_html_value, 'The form did not write the correct message.');

    // Check form elements load default values.
    $this->visit("/cp/settings/mailchimp");
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $field_value = $page->findField('api_key')->getValue();
    $this->assertSame('test1234', $field_value, 'Form is not loaded api key value.');
  }

  /**
   * Tests block visibility and modal popup.
   */
  public function testBlockVisibilityInContentRegion() {
    $web_assert = $this->assertSession();
    $this->visit("/");
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $is_exists = $page->hasContent('Mailchimp subscribe');
    $this->assertTrue($is_exists, 'Region not contains mailchimp block.');

    // Subscribe link is visible and press it.
    $submit_button = $page->findLink('Subscribe to list!');
    $submit_button->press();

    // Check modal is appeared.
    $result = $web_assert->waitForElementVisible('css', '.ui-dialog');
    $this->assertNotNull($result);
  }

}
