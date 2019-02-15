<?php

namespace Drupal\Tests\vsite_infinite_scroll\ExistingSite;

/**
 * Test the Infinite Scroll Vsite Views pager.
 *
 * @package Drupal\Tests\vsite\Kernel
 * @group vsite
 * @group kernel
 * @coversDefaultClass \Drupal\vsite_infinite_scroll\Plugin\CpSetting\VsitePagerSetting
 */
class VsiteCpSettingsTest extends VsiteInfiniteScrollExistingSiteTestBase {

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
   * Check vsite cp settings form submit.
   */
  public function testCpSettingsPageFormSave() {
    $this->drupalLogin($this->adminUser);
    $this->drupalPostForm('/cp/settings/vsite', [
      'long_list_content_pagination' => 'pager',
    ], 'Save configuration');
  }

}
