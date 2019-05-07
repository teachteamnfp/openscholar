<?php

namespace Drupal\Tests\os_breadcrumb\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Class BreadCrumbSettingFormTest.
 *
 * @group functional
 * @group breadcrumbs
 *
 * @package Drupal\Tests\os_breadcrumb\ExistingSite
 */
class BreadCrumbSettingFormTest extends ExistingSiteBase {

  /**
   * Admin user.
   *
   * @var \Drupal\user\Entity\User|false
   */
  protected $user;

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

    $this->user = $this->createUser([], '', TRUE);
    $this->simpleUser = $this->createUser();
  }

  /**
   * Test Settings form Access.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testBreadcrumbSettingsFormAccess() {
    $this->drupalLogin($this->simpleUser);
    $this->drupalGet('cp/settings/breadcrumb');
    // Testing Access.
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Test Settings form.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testBreadcrumbSettingsForm() {
    $this->drupalLogin($this->user);
    $this->drupalGet('cp/settings/breadcrumb');
    // Testing checked.
    $edit = [
      'show_breadcrumbs' => 'checked',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()
      ->checkboxChecked('show_breadcrumbs');

    $this->drupalGet('cp/settings/breadcrumb');
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
    $this->drupalLogin($this->user);

    // Test is visible.
    $this->drupalGet('cp/settings/breadcrumb');
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
