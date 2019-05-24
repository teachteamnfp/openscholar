<?php

namespace Drupal\Tests\os_breadcrumb\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class BreadCrumbSettingFormTest.
 *
 * @group functional
 * @group breadcrumbs
 *
 * @package Drupal\Tests\os_breadcrumb\ExistingSite
 */
class BreadCrumbSettingFormTest extends OsExistingSiteTestBase {

  /**
   * Group administrator.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $groupAdmin;

  /**
   * Simple user.
   *
   * @var \Drupal\user\Entity\User|false
   */
  protected $simpleUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->drupalLogin($this->groupAdmin);
  }

  /**
   * Test Settings form.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testBreadcrumbSettingsForm(): void {
    $this->drupalGet("{$this->group->get('path')->getValue()[0]['alias']}/cp/settings/breadcrumb");
    // Testing checked.
    $edit = [
      'show_breadcrumbs' => 'checked',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()
      ->checkboxChecked('show_breadcrumbs');

    $this->drupalGet("{$this->group->get('path')->getValue()[0]['alias']}/cp/settings/breadcrumb");
    // Testing unchecked.
    $edit = [
      'show_breadcrumbs' => FALSE,
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()
      ->checkboxNotchecked('show_breadcrumbs');
  }

  /**
   * Test Breadcrumb Visibility as per settings.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testBreadcrumbSettings(): void {
    // Test is visible.
    $this->drupalGet("{$this->group->get('path')->getValue()[0]['alias']}/cp/settings/breadcrumb");
    $edit = [
      'show_breadcrumbs' => TRUE,
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()->elementExists('css', 'ol.breadcrumb');

    // Test is not visible.
    $edit = [
      'show_breadcrumbs' => FALSE,
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()->elementNotExists('css', 'ol.breadcrumb');
  }

}
