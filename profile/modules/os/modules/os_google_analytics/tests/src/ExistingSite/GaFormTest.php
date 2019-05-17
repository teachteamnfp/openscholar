<?php

namespace Drupal\Tests\os_google_analytics\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class GaFormTest.
 *
 * @group functional
 * @group analytics
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 */
class GaFormTest extends OsExistingSiteTestBase {

  /**
   * Group administrator.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $groupAdmin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $group = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/test-alias',
      ],
    ]);
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $group);
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
  }

  /**
   * Test Setting form route.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testGaSettingsPath() {

    $this->drupalLogin($this->groupAdmin);
    $this->drupalGet('test-alias/cp/settings/analytics');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test Settings form.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testGaSettingsForm() {

    $this->drupalLogin($this->groupAdmin);
    $this->drupalGet('test-alias/cp/settings/analytics');
    // Dummy web property.
    $edit = [
      'edit-web-property-id' => 'UA-111111111-1',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()->fieldValueEquals('edit-web-property-id', 'UA-111111111-1');
  }

  /**
   * Test if vsite codes show on page in analytics script.
   *
   * @covers ::os_google_analytics_page_attachments
   */
  public function testVsiteCodesShowOnPage() {

    $this->drupalLogin($this->groupAdmin);
    // Test only vsite.
    $this->drupalGet('test-alias/cp/settings/analytics');
    // Dummy vsite web property.
    $edit = [
      'edit-web-property-id' => 'UA-111111111-1',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->drupalGet('test-alias');
    $this->assertSession()->responseContains('ga("send", "pageview")');
    $this->assertSession()->responseContains('UA-111111111-1');

    // Test both Global and vsite together.
    $this->drupalGet('admin/config/system/google-analytics');
    // Dummy global web property.
    $edit = [
      'google_analytics_account' => 'UA-111111111-2',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->drupalGet('test-alias');
    $this->assertSession()->responseContains('ga("test-alias.send", "pageview")');
    $this->assertSession()->responseContains('UA-111111111-1');
    $this->assertSession()->responseContains('UA-111111111-2');
  }

}
