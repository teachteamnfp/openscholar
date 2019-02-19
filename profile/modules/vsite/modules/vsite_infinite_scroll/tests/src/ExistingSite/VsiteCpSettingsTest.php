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
    $this->visit("/cp/settings/vsite");
    drupal_flush_all_caches();
    $this->visit("/cp/settings/vsite");
    $html = $this->getSession()->getPage()->getContent();
    $this->assertContains('Choose how long lists of content will display', $html);
    $this->drupalPostForm('/cp/settings/vsite', [
      'long_list_content_pagination' => 'pager',
    ], 'Save configuration');
    $this->visit("/cp/settings/vsite");
    $html = $this->getSession()->getPage()->getContent();
    $this->assertContains('name="long_list_content_pagination" value="pager" checked="checked"', $html, 'Default value is not set.');
  }

}
