<?php

namespace Drupal\Tests\os_redirect\ExistingSiteJavascript;

use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * Tests os_redirect module.
 *
 * @group functional-javascript
 * @group redirect
 */
class CpSettingsOsRedirectTest extends ExistingSiteWebDriverTestBase {

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
      'administer control panel redirect_maximum',
    ]);
  }

  /**
   * Tests os_redirect cp settings form behavior.
   */
  public function testCpSettingsFormSave() {
    $web_assert = $this->assertSession();
    $this->drupalLogin($this->adminUser);

    $this->visit("/cp/settings/redirect_maximum");
    $web_assert->statusCodeEquals(200);

    $edit = [
      'maximum_number' => 20,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $page = $this->getCurrentPage();
    $check_html_value = $page->hasContent('The configuration options have been saved.');
    $this->assertTrue($check_html_value, 'The form did not write the correct message.');

    // Check form elements load default values.
    $this->visit("/cp/settings/redirect_maximum");
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $field_value = $page->findField('maximum_number')->getValue();
    $this->assertSame('20', $field_value, 'Form is not loaded maximum_number value.');
  }

}
