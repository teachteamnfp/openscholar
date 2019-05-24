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
   * Group Admin user.
   *
   * @var \Drupal\user\Entity\User|false
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

    $this->simpleUser = $this->createUser();
  }

  /**
   * Test Settings form Access.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testBreadcrumbSettingsFormAccess() {
    $this->drupalLogin($this->simpleUser);
    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/breadcrumb");
    // Testing Access.
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Test Settings form.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testBreadcrumbSettingsForm() {
    $this->drupalLogin($this->groupAdmin);
    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/breadcrumb");
    // Testing checked.
    $edit = [
      'show_breadcrumbs' => 'checked',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()
      ->checkboxChecked('show_breadcrumbs');
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
  public function testBreadcrumbSettings() {
    $this->drupalLogin($this->groupAdmin);

    // Test is visible.
    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/breadcrumb");
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
