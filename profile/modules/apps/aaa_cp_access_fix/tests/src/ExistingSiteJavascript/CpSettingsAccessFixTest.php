<?php

namespace Drupal\Tests\aaa_cp_access_fix\ExistingSiteJavascript;

use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * Tests fix for cp settings page first access denied.
 *
 * All files should delete if the origin of problem is found.
 *
 * It starts with aaa at the directory to first run before all tests.
 *
 * @group testfix
 * @group functional-javascript
 */
class CpSettingsAccessFixTest extends ExistingSiteWebDriverTestBase {

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

    // Does not matter which cp setting is visited.
    $this->visit("/cp/settings/seo");
    $web_assert->statusCodeEquals(403);
    drupal_flush_all_caches();
    $this->visit("/cp/settings/seo");
    $web_assert->statusCodeEquals(200);
  }

}
