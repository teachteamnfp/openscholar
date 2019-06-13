<?php

namespace Drupal\Tests\vsite_infinite_scroll\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Test the Infinite Scroll Vsite Views pager.
 *
 * @package Drupal\Tests\vsite\Kernel
 * @group vsite
 * @group kernel
 * @coversDefaultClass \Drupal\vsite_infinite_scroll\Plugin\CpSetting\VsitePagerSetting
 */
class VsiteCpSettingsTest extends OsExistingSiteTestBase {

  /**
   * Group admin.
   *
   * @var \Drupal\user\Entity\User
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
   * Check vsite cp settings form submit.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testCpSettingsPageFormSave(): void {
    $this->drupalLogin($this->groupAdmin);
    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/vsite");
    $this->assertSession()->statusCodeEquals(200);
    $html = $this->getSession()->getPage()->getContent();
    $this->assertContains('Choose how long lists of content will display', $html);
    $this->drupalPostForm("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/vsite", [
      'long_list_content_pagination' => 'pager',
    ], 'Save configuration');
    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/vsite");
    $html = $this->getSession()->getPage()->getContent();
    $this->assertContains('name="long_list_content_pagination" value="pager" checked="checked"', $html, 'Default value is not set.');
  }

}
