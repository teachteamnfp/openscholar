<?php

namespace Drupal\Tests\vsite\ExistingSiteJavascript;

/**
 * Tests vsite module.
 *
 * @group vsite
 * @group functional-javascript
 * @coversDefaultClass \Drupal\vsite\Form\ConfigureSubSiteForm
 */
class VsiteConfigSubsiteAdminFormTest extends VsiteExistingSiteJavascriptTestBase {

  /**
   * Admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * Path to form URL.
   *
   * @var string
   */
  private $formUrl = '/admin/config/openscholar/vsite/subsite';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->adminUser = $this->createUser([
      'access administration pages',
      'access vsite settings',
      'bypass group access',
    ]);
  }

  /**
   * Tests config subsite admin form access denied.
   */
  public function testAccessDeniedAdminForm() {

    // Create a non-admin user.
    $user = $this->createUser();
    $this->drupalLogin($user);
    $this->visit($this->formUrl);

    $web_assert = $this->assertSession();

    $web_assert->statusCodeEquals(403);
  }

  /**
   * Tests config subsite admin form access with admin user.
   */
  public function testAccessAdminForm() {
    $this->drupalLogin($this->adminUser);
    $this->visit($this->formUrl);

    $web_assert = $this->assertSession();

    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextContains('Allowed parent sites group bundles');
    $web_assert->pageTextContains('Allowed sub sites group bundles');
  }

  /**
   * Tests put the same group type into parent and subsite.
   */
  public function testPostBothParentAndSubsiteAdminForm() {
    $this->drupalLogin($this->adminUser);
    $this->visit($this->formUrl);

    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);

    $edit = [
      'allowed_parent_sites[personal]' => 'personal',
      'allowed_sub_sites[personal]' => 'personal',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');
    $page = $this->getCurrentPage();
    $checkDefaultValue = $page->hasContent('Group types can not be both parent and sub site at the same time.');
    $this->assertTrue($checkDefaultValue, 'The form did not write the correct error message.');
  }

  /**
   * Tests field visibility on edit form.
   */
  public function testFieldVisibilityOnEditForm() {
    $this->drupalLogin($this->adminUser);
    $group_parent = $this->createGroup();
    $group_child = $this->createGroup([
      'type' => 'subsite_test',
    ]);
    $this->visit($this->formUrl);

    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);

    $edit = [
      'allowed_parent_sites[personal]' => 'personal',
      'allowed_sub_sites[subsite_test]' => 'subsite_test',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');
    $page = $this->getCurrentPage();
    $checkDefaultValue = $page->hasContent('Allowed values settings saved successful.');
    $this->assertTrue($checkDefaultValue, 'The form did not write the correct message.');

    // Check parent group edit form.
    $this->visit('/group/' . $group_parent->id() . '/edit');
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $checkDefaultValue = $page->hasContent('Parent site');
    $this->assertFalse($checkDefaultValue, 'Visible Parent site on Group parent edit form.');

    // Check child group edit form.
    $this->visit('/group/' . $group_child->id() . '/edit');
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $checkDefaultValue = $page->hasContent('Parent site');
    $this->assertTrue($checkDefaultValue, 'Hidden Parent site on Group parent edit form.');
  }

}
