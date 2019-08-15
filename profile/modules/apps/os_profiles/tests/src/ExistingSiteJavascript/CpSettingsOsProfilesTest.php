<?php

namespace Drupal\Tests\os_profiles\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Tests os_profiles module.
 *
 * @group functional-javascript
 * @group profiles
 */
class CpSettingsOsProfilesTest extends OsExistingSiteJavascriptTestBase {

  /**
   * Admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $groupAdmin;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->configFactory = $this->container->get('config.factory');
    $person = $this->createNode([
      'type' => 'person',
      'status' => 1,
      'field_first_name' => $this->randomMachineName(),
      'field_middle_name' => $this->randomMachineName(),
      'field_last_name' => $this->randomMachineName(),
    ]);
    $this->group->addContent($person, 'group_node:person');
  }

  /**
   * Tests cp settings form submit and default value.
   */
  public function testCpSettingsFormSave() {
    $web_assert = $this->assertSession();
    $this->drupalLogin($this->groupAdmin);

    $this->visitViaVsite("cp/settings/apps-settings/profiles", $this->group);
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $field_value = $page->findField('display_type')->getValue();
    $this->assertSame('teaser', $field_value, 'Form is not loaded default value.');

    $edit = [
      'display_type' => 'no_image_teaser',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $page = $this->getCurrentPage();
    $check_html_value = $page->hasContent('The configuration options have been saved.');
    $this->assertTrue($check_html_value, 'The form did not write the correct message.');

    // Check form elements load default values.
    $this->visitViaVsite("cp/settings/apps-settings/profiles", $this->group);
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $field_value = $page->findField('display_type')->getValue();
    $this->assertSame('no_image_teaser', $field_value, 'Form is not loaded api key value.');
  }

  /**
   * Test /people listing view mode.
   */
  public function testPeopleListingViewMode() {
    $web_assert = $this->assertSession();
    $this->drupalLogin($this->groupAdmin);
    // Make sure no_image_teaser is selected.
    $this->visitViaVsite("cp/settings/apps-settings/profiles", $this->group);
    $web_assert->statusCodeEquals(200);
    $edit = [
      'display_type' => 'no_image_teaser',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $this->visitViaVsite("people", $this->group);
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $article = $page->find('css', '.no-image-teaser');
    $this->assertNotNull($article);

  }

}
